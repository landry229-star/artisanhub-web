<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Retourne le nombre de notifications non lues + les 10 dernières.
     * Appelé par polling AJAX toutes les 30 secondes.
     */
    public function index()
    {
        $user = Auth::user();

        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'        => $n->id,
                'type'      => class_basename($n->type),
                'data'      => $n->data,
                'read'      => !is_null($n->read_at),
                'time'      => $n->created_at->diffForHumans(),
                'time_full' => $n->created_at->format('d/m/Y à H:i'),
            ]);

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markRead(string $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * Marquer toutes comme lues.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }
}
