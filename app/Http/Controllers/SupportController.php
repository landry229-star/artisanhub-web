<?php
// ══════════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/SupportController.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\SupportTicket;

class SupportController extends Controller
{
    public function index()
    {
        return view('pages.support');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email'],
            'subject' => ['required', 'in:paiement,commande,compte,remboursement,livreur,autre'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ], [
            'message.min' => 'Veuillez décrire votre problème en au moins 20 caractères.',
        ]);

        $subjectLabels = [
            'paiement'       => 'Problème de paiement',
            'commande'       => 'Problème de commande',
            'compte'         => 'Problème de compte',
            'remboursement'  => 'Demande de remboursement',
            'livreur'        => 'Problème de livraison',
            'autre'          => 'Autre demande',
        ];

        $ticket = SupportTicket::create([
            'number' => 'AH-' . now()->format('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'user_id' => auth()->id(),
            'email' => $validated['email'],
            'subject' => $subjectLabels[$validated['subject']],
            'message' => $validated['message'],
            'status' => 'open',
            'priority' => 'medium',
            'sla_due_at' => now()->addDays(2),
        ]);

        try {
            // Email à l'équipe support
            Mail::send('emails.support', [
                'senderName'    => $validated['name'],
                'senderEmail'   => $validated['email'],
                'subject'       => $subjectLabels[$validated['subject']],
                'userMessage'   => $validated['message'],
                'userId'        => auth()->id(),
                'ticket'        => $ticket,
            ], function ($m) use ($validated, $subjectLabels) {
                $m->to('support@artisanhub.bj', 'Support ArtisanHub')
                  ->replyTo($validated['email'], $validated['name'])
                  ->subject('[Support] ' . $subjectLabels[$validated['subject']] . ' — ' . $validated['name']);
            });

            // Email de confirmation à l'utilisateur
            Mail::send('emails.support_confirmation', [
                'name'    => $validated['name'],
                'subject' => $subjectLabels[$validated['subject']],
            ], function ($m) use ($validated) {
                $m->to($validated['email'], $validated['name'])
                  ->subject('✅ Votre demande a bien été reçue — ArtisanHub');
            });

        } catch (\Exception $e) {
            Log::error('Support email failed: ' . $e->getMessage());
            // On laisse passer : l'utilisateur verra un succès quand même
            // (mieux vaut ne pas exposer les erreurs d'envoi)
        }

        return back()->with('success', "Votre demande {$ticket->number} a bien été enregistrée. Nous vous répondrons sous 24h ouvrées.");
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// ROUTES À AJOUTER dans routes/web.php
// ══════════════════════════════════════════════════════════════════════════════
//
// use App\Http\Controllers\SupportController;
//
// // Pages légales (ajouter aux routes publiques existantes)
// Route::get('/mentions-legales',      fn() => view('pages.mentions'))->name('mentions');
// Route::get('/politique-remboursement', fn() => view('pages.remboursement'))->name('remboursement');
// Route::get('/support',               [SupportController::class, 'index'])->name('support');
// Route::post('/support',              [SupportController::class, 'send'])->name('support.send');
//
// ══════════════════════════════════════════════════════════════════════════════
