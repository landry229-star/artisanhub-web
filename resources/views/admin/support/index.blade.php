@extends('layouts.dashboard')

@section('title', 'Support admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Support admin</h1>
            <p class="text-muted mb-0">Tickets persistants, priorisation et réponse rapide.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.support.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Numéro, sujet, client, commande...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach(App\Models\SupportTicket::statusOptions() as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priorité</label>
                    <select name="priority" class="form-select">
                        <option value="">Toutes</option>
                        @foreach(App\Models\SupportTicket::priorityOptions() as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assigné à</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Tous</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected(request('assigned_to') == $admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary" type="submit">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Sujet</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Assigné</th>
                        <th>MAJ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td><strong>{{ $ticket->number }}</strong></td>
                            <td>
                                {{ $ticket->user?->name ?? $ticket->email }}
                                <div class="small text-muted">{{ $ticket->email }}</div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $ticket->subject }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 260px;">{{ $ticket->message }}</div>
                            </td>
                            <td>
                                @php($priorityColors = ['low' => 'secondary', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'dark'])
                                <span class="badge bg-{{ $priorityColors[$ticket->priority ?? 'medium'] }} text-uppercase">{{ $ticket->priority ?? 'medium' }}</span>
                            </td>
                            <td>
                                @php($statusColors = ['open' => 'primary', 'in_progress' => 'info', 'resolved' => 'success', 'closed' => 'secondary'])
                                <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'primary' }}">{{ App\Models\SupportTicket::statusOptions()[$ticket->status] ?? $ticket->status }}</span>
                            </td>
                            <td>{{ $ticket->assignedTo?->name ?? 'Non assigné' }}</td>
                            <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucun ticket de support ne correspond au filtre.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
