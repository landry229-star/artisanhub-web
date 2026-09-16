@extends('layouts.dashboard')
@section('title', 'Réclamations garantie')
@section('page-title', 'Réclamations "Satisfait ou repris"')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    @php $kpis = [
        ['label'=>'En attente', 'value'=>$stats['pending'],  'icon'=>'bi-hourglass',    'bg'=>'#FFF3CD','ic'=>'#856404'],
        ['label'=>'Approuvées', 'value'=>$stats['approved'], 'icon'=>'bi-check-circle', 'bg'=>'#D4EDDA','ic'=>'#155724'],
        ['label'=>'Rejetées',   'value'=>$stats['rejected'], 'icon'=>'bi-x-circle',     'bg'=>'#F8D7DA','ic'=>'#721C24'],
    ]; @endphp
    @foreach($kpis as $k)
        <div class="col-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-icon mb-3" style="background:{{ $k['bg'] }}">
                    <i class="bi {{ $k['icon'] }}" style="color:{{ $k['ic'] }}"></i>
                </div>
                <div class="stat-value" style="font-size:1.1rem">{{ $k['value'] }}</div>
                <div class="stat-label">{{ $k['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card">

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th><th>Commande</th><th>Client</th><th>Artisan</th>
                    <th>Motif</th><th>Preuve</th><th>Statut</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($claims as $claim)
                    <tr>
                        <td class="text-muted" style="font-size:.8rem">#{{ $claim->id }}</td>
                        <td>
                            <a href="{{ route('admin.orders.index', ['search' => $claim->order->title]) }}" style="font-size:.82rem">
                                #{{ $claim->order_id }} — {{ Str::limit($claim->order->title, 25) }}
                            </a>
                        </td>
                        <td><span style="font-size:.82rem">{{ $claim->client->name }}</span></td>
                        <td><span style="font-size:.82rem">{{ $claim->artisan->name }}</span></td>
                        <td><span style="font-size:.8rem">{{ Str::limit($claim->reason, 40) }}</span></td>
                        <td>
                            @if($claim->evidence_path)
                                <a href="{{ route('files.claim', $claim) }}" target="_blank" class="btn btn-sm btn-outline-clay" style="font-size:.72rem">
                                    <i class="bi bi-image me-1"></i>Voir
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status status-{{ $claim->status === 'pending' ? 'en_attente' : ($claim->status === 'approved' ? 'terminee' : 'annulee') }}">
                                {{ $claim->refundIsPending() ? 'Remb. en attente' : ['pending'=>'En attente','approved'=>'Approuvée','rejected'=>'Rejetée'][$claim->status] }}
                            </span>
                            @if($claim->status !== 'pending' && $claim->refund_amount)
                                <div class="text-muted mt-1" style="font-size:.72rem">{{ number_format($claim->refund_amount,0,',',' ') }} XOF</div>
                            @endif
                        </td>
                        <td><span class="text-muted" style="font-size:.78rem">{{ $claim->created_at->format('d/m/Y') }}</span></td>
                        <td>
                            @if($claim->isPending())
                                <button class="btn btn-sm btn-clay" style="font-size:.75rem"
                                        data-bs-toggle="modal" data-bs-target="#claimModal{{ $claim->id }}">
                                    <i class="bi bi-gavel me-1"></i>Traiter
                                </button>
                            @elseif($claim->refundIsPending())
                                <form action="{{ route('admin.guarantee-claims.confirm-refund', $claim) }}" method="POST"
                                      onsubmit="return confirm('Confirmer que le remboursement de {{ $claim->refund_amount }} XOF a bien été effectué ?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-danger" style="font-size:.75rem">
                                        <i class="bi bi-check2-circle me-1"></i>Confirmer remboursé
                                    </button>
                                </form>
                            @else
                                <span class="text-muted" style="font-size:.75rem">{{ $claim->admin_note ? Str::limit($claim->admin_note, 25) : '—' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">Aucune réclamation de garantie</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @forelse($claims as $claim)
            <div class="mcard">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="fw-700" style="font-size:.9rem">Commande #{{ $claim->order_id }}</div>
                    <span class="badge-status status-{{ $claim->status === 'pending' ? 'en_attente' : ($claim->status === 'approved' ? 'terminee' : 'annulee') }}">
                        {{ $claim->refundIsPending() ? 'Remb. en attente' : ['pending'=>'En attente','approved'=>'Approuvée','rejected'=>'Rejetée'][$claim->status] }}
                    </span>
                </div>
                <div class="mcard-row"><span class="label">Client</span><span>{{ $claim->client->name }}</span></div>
                <div class="mcard-row"><span class="label">Artisan</span><span>{{ $claim->artisan->name }}</span></div>
                <div class="mcard-row"><span class="label">Motif</span><span>{{ Str::limit($claim->reason, 30) }}</span></div>
                <div class="mcard-row"><span class="label">Date</span><span>{{ $claim->created_at->format('d/m/Y') }}</span></div>
                @if($claim->evidence_path)
                    <div class="mcard-row"><span class="label">Preuve</span>
                        <a href="{{ route('files.claim', $claim) }}" target="_blank">Voir la photo</a>
                    </div>
                @endif

                @if($claim->isPending())
                    <div class="mcard-actions">
                        <button class="btn btn-sm btn-clay" data-bs-toggle="modal" data-bs-target="#claimModal{{ $claim->id }}">
                            <i class="bi bi-gavel me-1"></i>Traiter la réclamation
                        </button>
                    </div>
                @elseif($claim->refundIsPending())
                    <div class="mcard-actions">
                        <form action="{{ route('admin.guarantee-claims.confirm-refund', $claim) }}" method="POST"
                              onsubmit="return confirm('Confirmer que le remboursement de {{ $claim->refund_amount }} XOF a bien été effectué ?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-check2-circle me-1"></i>Confirmer remboursé
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-center text-muted py-4">Aucune réclamation de garantie</p>
        @endforelse
    </div>

    {{-- Modals de traitement (une seule fois, en dehors des deux vues) --}}
    @foreach($claims as $claim)
        @if($claim->isPending())
            <div class="modal fade" id="claimModal{{ $claim->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-700">Réclamation #{{ $claim->id }} — Commande #{{ $claim->order_id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-3 rounded-3 mb-3" style="background:#F5EFE6">
                                <strong>{{ $claim->client->name }}</strong> vs <strong>{{ $claim->artisan->name }}</strong><br>
                                <small class="text-muted">{{ $claim->reason }}</small>
                            </div>

                            @php $maxRefund = (int) ($claim->order->payment->guarantee_contribution ?? 0); @endphp
                            <p class="text-muted mb-3" style="font-size:.8rem">
                                Fonds de garantie disponible pour cette commande : <strong>{{ number_format($maxRefund,0,',',' ') }} XOF</strong> maximum.
                            </p>

                            {{-- Approuver --}}
                            <form action="{{ route('admin.guarantee-claims.approve', $claim) }}" method="POST" class="mb-3">
                                @csrf @method('PATCH')
                                <div class="mb-2">
                                    <label class="form-label fw-600" style="font-size:.85rem">Montant à rembourser (XOF)</label>
                                    <input type="number" name="refund_amount" class="form-control" min="1" max="{{ $maxRefund }}" value="{{ $maxRefund }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-600" style="font-size:.85rem">Note (optionnelle)</label>
                                    <textarea name="admin_note" class="form-control" rows="2" placeholder="Justification..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100"
                                        onclick="return confirm('Approuver et rembourser le client ?')">
                                    <i class="bi bi-check-circle me-1"></i>Approuver le remboursement
                                </button>
                            </form>

                            <hr style="border-color:#ECD8C6">

                            {{-- Rejeter --}}
                            <form action="{{ route('admin.guarantee-claims.reject', $claim) }}" method="POST">
                                @csrf @method('PATCH')
                                <div class="mb-2">
                                    <label class="form-label fw-600" style="font-size:.85rem">Motif du rejet <span class="text-danger">*</span></label>
                                    <textarea name="admin_note" class="form-control" rows="2" placeholder="Expliquez pourquoi la réclamation est rejetée..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-outline-danger w-100"
                                        onclick="return confirm('Rejeter cette réclamation ?')">
                                    <i class="bi bi-x-circle me-1"></i>Rejeter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="mt-3">{{ $claims->links() }}</div>
</div>
@endsection
