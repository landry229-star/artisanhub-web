<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedTo', 'order'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('id', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tickets = $query->paginate(20)->withQueryString();
        $admins = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.support.index', compact('tickets', 'admins'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignedTo', 'order', 'messages.sender']);
        $admins = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.support.show', compact('ticket', 'admins'));
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
        ]);

        $ticket->update([
            'assigned_to' => $validated['assigned_to'] ?? $ticket->assigned_to,
            'priority' => $validated['priority'] ?? $ticket->priority,
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        return back()->with('success', 'Le ticket a été réaffecté avec succès.');
    }

    public function status(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'priority' => $validated['priority'] ?? $ticket->priority,
            'internal_note' => $validated['internal_note'] ?? $ticket->internal_note,
            'resolved_at' => $validated['status'] === 'resolved' || $validated['status'] === 'closed'
                ? ($ticket->resolved_at ?? now())
                : null,
        ]);

        return back()->with('success', 'Le statut du ticket a été mis à jour.');
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:4000'],
        ]);

        $message = SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'admin',
            'body' => $validated['message'],
        ]);

        $ticket->update([
            'admin_reply' => $validated['message'],
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
            'last_reply_at' => now(),
            'resolved_at' => null,
        ]);

        return back()->with('success', 'La réponse a bien été enregistrée dans le ticket #' . $ticket->number . '.');
    }
}
