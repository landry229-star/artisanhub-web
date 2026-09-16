@extends('layouts.dashboard')
@section('title', 'Commandes')
@section('page-title', 'Gestion des commandes')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.orders.export') }}" class="btn btn-sm btn-outline-clay">
        <i class="bi bi-download me-1"></i>Exporter CSV
    </a>
@endsection

@section('content')
<div class="content-card">
    <form method="GET" class="row g-2 mb-4">
        <div class="col-12 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                @foreach(['en_attente'=>'En attente','acceptee'=>'Acceptée','en_cours'=>'En cours','livree'=>'Livrée','terminee'=>'Terminée','annulee'=>'Annulée','litige'=>'Litige'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('status')===$v ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-5">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Titre commande..."
                       value="{{ request('search') }}">
                <button class="btn btn-clay" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th><th>Titre</th><th>Client</th><th>Artisan</th>
                    <th>Budget</th><th>Statut</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="text-muted" style="font-size:.8rem">#{{ $order->id }}</td>
                        <td>
                            <div class="fw-600" style="font-size:.875rem">{{ Str::limit($order->title, 35) }}</div>
                        </td>
                        <td><span style="font-size:.82rem">{{ $order->client->name }}</span></td>
                        <td><span style="font-size:.82rem">{{ $order->artisan->name }}</span></td>
                        <td>
                            @if($order->budget)
                                <span class="fw-600 text-clay" style="font-size:.82rem">
                                    {{ number_format($order->budget,0,',',' ') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                        <td><span class="text-muted" style="font-size:.78rem">{{ $order->created_at->format('d/m/Y') }}</span></td>
                        <td>
                            @if($order->status === 'litige')
                                <button class="btn btn-sm btn-clay" style="font-size:.75rem"
                                        data-bs-toggle="modal" data-bs-target="#arbitrateModal{{ $order->id }}">
                                    <i class="bi bi-hammer me-1"></i>Arbitrer
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">Aucune commande trouvée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @forelse($orders as $order)
            <div class="mcard">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="fw-700" style="font-size:.9rem">{{ Str::limit($order->title, 35) }}</div>
                    <span class="text-muted" style="font-size:.75rem">#{{ $order->id }}</span>
                </div>
                <div class="mcard-row"><span class="label">Client</span><span>{{ $order->client->name }}</span></div>
                <div class="mcard-row"><span class="label">Artisan</span><span>{{ $order->artisan->name }}</span></div>
                <div class="mcard-row">
                    <span class="label">Budget</span>
                    <span>
                        @if($order->budget)
                            {{ number_format($order->budget,0,',',' ') }} XOF
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="mcard-row">
                    <span class="label">Statut</span>
                    <span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                </div>
                <div class="mcard-row"><span class="label">Date</span><span>{{ $order->created_at->format('d/m/Y') }}</span></div>

                @if($order->status === 'litige')
                    <div class="mcard-actions">
                        <button class="btn btn-sm btn-clay" data-bs-toggle="modal" data-bs-target="#arbitrateModal{{ $order->id }}">
                            <i class="bi bi-hammer me-1"></i>Arbitrer le litige
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-muted py-4">Aucune commande trouvée</p>
        @endforelse
    </div>

    {{-- Modals d'arbitrage (une seule fois, en dehors des deux vues) --}}
    @foreach($orders as $order)
        @if($order->status === 'litige')
            <div class="modal fade" id="arbitrateModal{{ $order->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-700">Arbitrer le litige #{{ $order->id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.orders.arbitrate', $order) }}" method="POST">
                            @csrf @method('PATCH')
                            <div class="modal-body">
                                <div class="p-3 rounded-3 mb-3" style="background:#F5EFE6">
                                    <strong>{{ $order->title }}</strong><br>
                                    <small class="text-muted">{{ $order->client->name }} → {{ $order->artisan->name }}</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-600">Décision <span class="text-danger">*</span></label>
                                    <select name="decision" class="form-select" required>
                                        <option value="">Choisir une décision</option>
                                        <option value="terminee">✅ Valider la livraison (payer l'artisan)</option>
                                        <option value="annulee">❌ Annuler (rembourser le client)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-600">Note d'arbitrage <span class="text-danger">*</span></label>
                                    <textarea name="admin_note" class="form-control" rows="3"
                                              placeholder="Expliquez votre décision aux deux parties..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-clay">Confirmer la décision</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
