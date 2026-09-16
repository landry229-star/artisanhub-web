@extends('layouts.dashboard')

@section('title', 'Ticket support')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Ticket {{ $ticket->number }}</h1>
            <p class="text-muted mb-0">{{ $ticket->subject }}</p>
        </div>
        <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Conversation</strong>
                    <span class="badge bg-primary-subtle text-primary">{{ App\Models\SupportTicket::statusOptions()[$ticket->status] ?? $ticket->status }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 border rounded bg-light-subtle">
                        <div class="small text-muted mb-2">Message client</div>
                        <p class="mb-0 whitespace-pre-line">{{ $ticket->message }}</p>
                    </div>

                    @foreach($ticket->messages as $message)
                        <div class="mb-3 p-3 border rounded {{ $message->sender_type === 'admin' ? 'bg-primary-subtle' : 'bg-light' }} ">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>{{ $message->sender_type === 'admin' ? ($message->sender?->name ?? 'Équipe support') : ($ticket->user?->name ?? $ticket->email) }}</strong>
                                <small class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div>{{ $message->body }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <strong>Réponse admin</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" placeholder="Renseignez la réponse au client..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><strong>Informations</strong></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Client</dt>
                        <dd class="col-7">{{ $ticket->user?->name ?? $ticket->email }}</dd>
                        <dt class="col-5">Email</dt>
                        <dd class="col-7">{{ $ticket->email }}</dd>
                        <dt class="col-5">Commande</dt>
                        <dd class="col-7">{{ $ticket->order ? '#' . $ticket->order->id . ' — ' . $ticket->order->title : '—' }}</dd>
                        <dt class="col-5">Priorité</dt>
                        <dd class="col-7">{{ App\Models\SupportTicket::priorityOptions()[$ticket->priority] ?? $ticket->priority }}</dd>
                        <dt class="col-5">SLA</dt>
                        <dd class="col-7">{{ $ticket->sla_due_at ? $ticket->sla_due_at->format('d/m/Y H:i') : 'Non défini' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><strong>Assignation</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.support.assign', $ticket) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Agent</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Non assigné</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" @selected($ticket->assigned_to == $admin->id)>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priorité</label>
                            <select name="priority" class="form-select">
                                @foreach(App\Models\SupportTicket::priorityOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected($ticket->priority === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">Enregistrer</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><strong>État</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.support.status', $ticket) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select">
                                @foreach(App\Models\SupportTicket::statusOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note interne</label>
                            <textarea name="internal_note" rows="3" class="form-control" placeholder="Note de suivi interne...">{{ $ticket->internal_note }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
