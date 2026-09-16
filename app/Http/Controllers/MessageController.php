<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Message;
use App\Services\TranslationService;
use App\Services\TranscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct(
        private TranslationService $translator,
        private TranscriptionService $transcriber,
    ) {}

    /** Redirige vers la conversation par défaut (rétrocompatibilité des anciens liens). */
    public function index(Order $order)
    {
        abort_if(!in_array(Auth::id(), $order->chatParticipantIds()), 403);

        $contact = $order->defaultChatContactFor(Auth::user());
        abort_if(!$contact, 404, 'Aucun interlocuteur disponible pour le moment.');

        return redirect()->route('messages.thread', [$order, $contact]);
    }

    /**
     * Conversation privée entre l'utilisateur connecté et $contact, dans le
     * cadre de $order. Chaque paire (client↔artisan, artisan↔livreur,
     * livreur↔client) a sa propre conversation : personne ne voit les
     * messages échangés entre les deux autres.
     */
    public function thread(Order $order, User $contact)
    {
        $me = Auth::id();
        abort_if(!in_array($me, $order->chatParticipantIds()), 403);
        abort_if(!in_array($contact->id, $order->chatParticipantIds()) || $contact->id === $me, 404);

        Message::where('order_id', $order->id)
            ->betweenPair($me, $contact->id)
            ->where('recipient_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where('order_id', $order->id)
            ->betweenPair($me, $contact->id)
            ->with(['sender', 'recipient'])
            ->orderBy('created_at')
            ->latest()
            ->paginate(100)
            ->withQueryString();

        $otherContacts = $order->chatContactsFor(Auth::user())
            ->reject(fn ($u) => $u->id === $contact->id);

        return view('messages.index', compact('order', 'contact', 'messages', 'otherContacts'));
    }

    public function store(Request $request, Order $order, User $contact)
    {
        $me = Auth::id();
        abort_if(!in_array($me, $order->chatParticipantIds()), 403);
        abort_if(!in_array($contact->id, $order->chatParticipantIds()) || $contact->id === $me, 404);
        abort_if(in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED]), 422,
            'Impossible d\'envoyer un message sur une commande terminée ou annulée.');

        $request->validate([
            'body'           => ['required_without_all:attachment,audio', 'nullable', 'string', 'max:2000'],
            'attachment'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'audio'          => ['nullable', 'file', 'mimes:webm,ogg,mp3,mp4,wav,m4a,mpga', 'max:8192'],
            'audio_duration' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('messages', 'local');
        }

        $audioPath = null;
        $audioTranscript = null;
        if ($request->hasFile('audio')) {
            $audioFile = $request->file('audio');
            $audioPath = $audioFile->store('messages/audio', 'local');
            $audioTranscript = $this->transcriber->transcribe($audioFile->getRealPath());
        }

        $suspect = Message::detectSuspect((string) ($request->body ?: $audioTranscript ?: '')) ?: null;

        $sender = Auth::user();
        $textToTranslate = $request->body ?: $audioTranscript;

        $translatedBody = null;
        $translatedLang = null;
        if ($textToTranslate && $contact->preferred_language !== $sender->preferred_language) {
            $translatedBody = $this->translator->translate(
                $textToTranslate,
                $contact->preferred_language,
                $sender->preferred_language
            );
            $translatedLang = $translatedBody ? $contact->preferred_language : null;
        }

        $message = Message::create([
            'order_id'         => $order->id,
            'sender_id'        => $me,
            'recipient_id'     => $contact->id,
            'body'             => $request->body,
            'attachment_path'  => $attachmentPath,
            'audio_path'       => $audioPath,
            'audio_duration'   => $request->integer('audio_duration') ?: null,
            'audio_transcript' => $audioTranscript,
            'translated_body'  => $translatedBody,
            'translated_lang'  => $translatedLang,
            'is_flagged'       => (bool) $suspect,
            'flag_reason'      => $suspect ? 'Détection auto : contient "' . $suspect . '"' : null,
            'flagged_at'       => $suspect ? now() : null,
        ]);

        if ($suspect) {
            User::where('role', 'admin')->get()->each(function ($admin) use ($message) {
                try {
                    $admin->notify(new \App\Notifications\MessageAutoFlagged($message));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Notification auto-flag admin : ' . $e->getMessage());
                }
            });
        }

        return back()->with('success', 'Message envoyé.');
    }
}
