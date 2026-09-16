<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /** Liste des conversations avec filtre suspect/alertes */
    public function index(Request $request)
    {
        $query = Order::with(['client', 'artisan'])
            ->withCount('messages')
            ->withCount(['messages as flagged_messages_count' => fn ($q) => $q->where('is_flagged', true)])
            ->whereIn('status', ['acceptee','en_cours','livree','terminee','litige']);

        // Filtre conversations suspectes uniquement
        if ($request->boolean('flagged')) {
            $query->whereHas('messages', fn($q) => $q->where('is_flagged', true));
        }

        // Filtre avec alerte active
        if ($request->boolean('alerted')) {
            $query->where('has_alert', true);
        }

        // Filtre par artisan
        if ($request->filled('artisan')) {
            $query->whereHas('artisan', fn($q) => $q->where('name', 'like', '%'.$request->artisan.'%'));
        }

        $orders = $query->latest()->paginate(20);

        // Statistiques modération
        $stats = [
            'total_convs'    => Order::whereIn('status', ['acceptee','en_cours','livree','terminee'])->count(),
            'flagged_msgs'   => Message::where('is_flagged', true)->count(),
            'alerted_orders' => Order::where('has_alert', true)->count(),
            'today_messages' => Message::whereDate('created_at', today())->count(),
        ];

        return view('admin.messages.index', compact('orders', 'stats'));
    }

    /** Voir une conversation complète */
    public function show(Order $order)
    {
        $order->load('client', 'artisan', 'messages.sender', 'messages.flaggedBy');
        $messages = $order->messages()->with('sender', 'flaggedBy')->latest()->limit(500)->get();

        // Auto-détection : marquer automatiquement les messages suspects non encore flaggés
        $autoFlagged = 0;
        foreach ($messages as $msg) {
            if (!$msg->isFlagged()) {
                $suspect = Message::detectSuspect($msg->body);
                if ($suspect) {
                    $msg->update([
                        'is_flagged'  => true,
                        'flag_reason' => 'Détection auto : contient "' . $suspect . '"',
                        'flagged_by'  => Auth::id(),
                        'flagged_at'  => now(),
                    ]);
                    $autoFlagged++;
                }
            }
        }

        // Recharger après les mises à jour auto
        $messages = $order->messages()->with('sender', 'flaggedBy')->latest()->limit(500)->get();

        return view('admin.messages.show', compact('order', 'messages', 'autoFlagged'));
    }

    /** Signaler manuellement un message */
    public function flag(Request $request, Message $message)
    {
        $request->validate([
            'flag_reason' => ['required', 'string', 'max:255'],
        ]);

        $message->update([
            'is_flagged'  => true,
            'flag_reason' => $request->flag_reason,
            'flagged_by'  => Auth::id(),
            'flagged_at'  => now(),
        ]);

        return back()->with('success', '⚠️ Message signalé.');
    }

    /** Désignaler un message */
    public function unflag(Message $message)
    {
        $message->update([
            'is_flagged'  => false,
            'flag_reason' => null,
            'flagged_by'  => null,
            'flagged_at'  => null,
        ]);

        return back()->with('success', 'Signalement retiré.');
    }

    /** Envoyer une alerte au client sur une commande suspecte */
    public function alert(Request $request, Order $order)
    {
        $request->validate([
            'alert_message' => ['required', 'string', 'max:500'],
        ]);

        $order->update([
            'has_alert'     => true,
            'alert_message' => $request->alert_message,
            'alerted_at'    => now(),
        ]);

        // Notifier le client par email
        try {
            $order->client->notify(new \App\Notifications\OrderAlert($order));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification alerte : ' . $e->getMessage());
        }

        return back()->with('success', '🔔 Alerte envoyée au client.');
    }

    /** Retirer l'alerte sur une commande */
    public function clearAlert(Order $order)
    {
        $order->update([
            'has_alert'     => false,
            'alert_message' => null,
            'alerted_at'    => null,
        ]);

        return back()->with('success', 'Alerte retirée.');
    }

    /** Supprimer un message */
    public function destroy(Message $message)
    {
        $orderId = $message->order_id;
        $message->delete();
        return redirect()->route('admin.messages.show', $orderId)
            ->with('success', 'Message supprimé.');
    }
}
