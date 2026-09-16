@extends('layouts.dashboard')
@section('title', 'Reversements')
@section('page-title', 'Revenus & Reversements')

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
        ['label'=>'Commissions totales',    'value'=>number_format($stats['total_commission'],0,',',' ').' XOF', 'icon'=>'bi-cash-coin',    'bg'=>'#D4EDDA','ic'=>'#155724'],
        ['label'=>'Ce mois-ci',             'value'=>number_format($stats['month_commission'],0,',',' ').' XOF', 'icon'=>'bi-calendar-month','bg'=>'#F5EFE6','ic'=>'#C4622D'],
        ['label'=>'Volume total traité',    'value'=>number_format($stats['total_volume'],0,',',' ').' XOF',     'icon'=>'bi-graph-up',      'bg'=>'#CCE5FF','ic'=>'#004085'],
        ['label'=>'À reverser aux artisans','value'=>number_format($stats['pending_reversals'],0,',',' ').' XOF','icon'=>'bi-hourglass',    'bg'=>'#FFF3CD','ic'=>'#856404'],
    ]; @endphp
    @foreach($kpis as $k)
        <div class="col-6 col-lg-3">
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

{{-- ★ REVERSEMENTS EN ATTENTE --}}
@if($pendingReversals->isNotEmpty())
<div class="content-card mb-4" style="border:2px solid #FFF3CD">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-700 mb-0">
            <i class="bi bi-hourglass-split me-2" style="color:#856404"></i>
            Reversements à faire ({{ $pendingReversals->count() }} artisan(s))
        </h5>
        <span class="badge px-3 py-2" style="background:#FFF3CD;color:#856404;font-size:.85rem">
            Total : {{ number_format($stats['pending_reversals'],0,',',' ') }} XOF
        </span>
    </div>

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle" style="font-size:.875rem">
            <thead>
                <tr style="border-bottom:2px solid #ECD8C6">
                    <th>Artisan</th>
                    <th>Ville</th>
                    <th>Téléphone</th>
                    <th>Nb commandes</th>
                    <th>Solde à reverser</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pendingReversals as $artisan)
            <tr style="border-bottom:1px solid #f0e8de;vertical-align:middle">
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle"
                             width="36" height="36" style="object-fit:cover;flex-shrink:0">
                        <div>
                            <div class="fw-700">{{ $artisan->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $artisan->artisanProfile?->specialty }}
                            </div>
                        </div>
                    </div>
                </td>
                <td>📍 {{ $artisan->city }}</td>
                <td>
                    @if($artisan->phone)
                        <a href="tel:{{ $artisan->phone }}" class="text-decoration-none fw-600" style="color:#C4622D">
                            📞 {{ $artisan->phone }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge" style="background:#ECD8C6;color:#5C3D1E">
                        {{ $artisan->nb_paiements }}
                    </span>
                </td>
                <td>
                    <span class="fw-700" style="color:#155724;font-size:1rem">
                        {{ number_format($artisan->solde_a_reverser,0,',',' ') }} XOF
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-success py-1 px-2"
                            style="font-size:.78rem"
                            data-bs-toggle="modal"
                            data-bs-target="#reverseModal{{ $artisan->id }}">
                        <i class="bi bi-send-check me-1"></i>Lancer le reversement
                    </button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @foreach($pendingReversals as $artisan)
        <div class="mcard">
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle"
                     width="42" height="42" style="object-fit:cover;flex-shrink:0">
                <div class="flex-grow-1" style="min-width:0">
                    <div class="fw-700" style="font-size:.92rem">{{ $artisan->name }}</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $artisan->artisanProfile?->specialty }}</div>
                </div>
            </div>
            <div class="mcard-row"><span class="label">Ville</span><span>📍 {{ $artisan->city }}</span></div>
            <div class="mcard-row">
                <span class="label">Téléphone</span>
                @if($artisan->phone)
                    <a href="tel:{{ $artisan->phone }}" class="text-decoration-none fw-600" style="color:#C4622D">📞 {{ $artisan->phone }}</a>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
            <div class="mcard-row">
                <span class="label">Nb commandes</span>
                <span class="badge" style="background:#ECD8C6;color:#5C3D1E">{{ $artisan->nb_paiements }}</span>
            </div>
            <div class="mcard-row">
                <span class="label">Solde à reverser</span>
                <span class="fw-700" style="color:#155724">{{ number_format($artisan->solde_a_reverser,0,',',' ') }} XOF</span>
            </div>
            <div class="mcard-actions">
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#reverseModal{{ $artisan->id }}">
                    <i class="bi bi-send-check me-1"></i>Lancer le reversement
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Modals de confirmation (une seule fois, hors du tableau — corrige un bug de HTML invalide) --}}
    @foreach($pendingReversals as $artisan)
    <div class="modal fade" id="reverseModal{{ $artisan->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-700">Lancer le reversement Mobile Money</h6>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.wallet.reverse', $artisan) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body pt-2">
                        <div class="p-3 rounded-3 mb-3" style="background:#f0f7f0;border:1px solid #c3e6cb">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Artisan</span>
                                <strong>{{ $artisan->name }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Téléphone</span>
                                <strong>{{ $artisan->phone ?? 'Non renseigné' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Montant à verser</span>
                                <strong style="color:#155724;font-size:1.1rem">
                                    {{ number_format($artisan->solde_a_reverser,0,',',' ') }} XOF
                                </strong>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-600">Note de reversement</label>
                            <input type="text" name="reversal_note" class="form-control"
                                   placeholder="Ex: Versé via MTN MoMo le {{ now()->format('d/m/Y') }}"
                                   maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-send-check me-1"></i>Lancer le reversement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="content-card mb-4" style="background:#D4EDDA;border:1px solid #c3e6cb">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-check-circle-fill" style="font-size:1.8rem;color:#155724;flex-shrink:0"></i>
        <div>
            <p class="fw-700 mb-0" style="color:#155724">Tous les artisans ont été payés ✅</p>
            <p class="mb-0 text-muted" style="font-size:.85rem">Aucun reversement en attente.</p>
        </div>
    </div>
</div>
@endif

{{-- ★ REMBOURSEMENTS EN ATTENTE (arbitrage litige) --}}
@if($pendingRefunds->isNotEmpty())
<div class="content-card mb-4" style="border:2px solid #F8D7DA">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-700 mb-0">
            <i class="bi bi-arrow-counterclockwise me-2" style="color:#721C24"></i>
            Remboursements à confirmer ({{ $pendingRefunds->count() }})
        </h5>
        <span class="badge px-3 py-2" style="background:#F8D7DA;color:#721C24;font-size:.85rem">
            Total : {{ number_format($pendingRefunds->sum('amount'),0,',',' ') }} XOF
        </span>
    </div>
    <p class="text-muted mb-3" style="font-size:.82rem">
        Ces remboursements ont été validés suite à un arbitrage de litige. Lance le dépôt
        Mobile Money depuis cet écran ; le statut et l'identifiant FedaPay seront conservés.
    </p>

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle" style="font-size:.875rem">
            <thead>
                <tr style="border-bottom:2px solid #ECD8C6">
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Demandé par</th>
                    <th>Date demande</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pendingRefunds as $payment)
            <tr style="border-bottom:1px solid #f0e8de;vertical-align:middle">
                <td>
                    <a href="{{ route('admin.orders.index', ['search' => $payment->order->title]) }}">
                        #{{ $payment->order_id }} — {{ Str::limit($payment->order->title, 22) }}
                    </a>
                </td>
                <td>{{ $payment->order->client->name }}</td>
                <td>
                    <span class="fw-700" style="color:#721C24;font-size:1rem">
                        {{ number_format($payment->amount,0,',',' ') }} XOF
                    </span>
                </td>
                <td>{{ $payment->refundRequestedBy?->name ?? '—' }}</td>
                <td class="text-muted">{{ $payment->refund_requested_at->format('d/m/Y') }}</td>
                <td>
                    <button class="btn btn-sm btn-danger py-1 px-2"
                            style="font-size:.78rem"
                            data-bs-toggle="modal"
                            data-bs-target="#refundModal{{ $payment->id }}">
                        <i class="bi bi-arrow-repeat me-1"></i>Lancer le remboursement
                    </button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @foreach($pendingRefunds as $payment)
        <div class="mcard">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="fw-700" style="font-size:.9rem">{{ Str::limit($payment->order->title, 22) }}</div>
                <span class="text-muted" style="font-size:.75rem">#{{ $payment->order_id }}</span>
            </div>
            <div class="mcard-row"><span class="label">Client</span><span>{{ $payment->order->client->name }}</span></div>
            <div class="mcard-row"><span class="label">Montant</span><span class="fw-700" style="color:#721C24">{{ number_format($payment->amount,0,',',' ') }} XOF</span></div>
            <div class="mcard-row"><span class="label">Demandé par</span><span>{{ $payment->refundRequestedBy?->name ?? '—' }}</span></div>
            <div class="mcard-row"><span class="label">Date</span><span>{{ $payment->refund_requested_at->format('d/m/Y') }}</span></div>
            <div class="mcard-actions">
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#refundModal{{ $payment->id }}">
                    <i class="bi bi-arrow-repeat me-1"></i>Lancer le remboursement
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Modals de confirmation --}}
    @foreach($pendingRefunds as $payment)
    <div class="modal fade" id="refundModal{{ $payment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-700">Confirmer le remboursement</h6>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.wallet.confirm-refund', $payment) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body pt-2">
                        <div class="p-3 rounded-3 mb-3" style="background:#faf0f1;border:1px solid #f1c2c8">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Client</span>
                                <strong>{{ $payment->order->client->name }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Montant remboursé</span>
                                <strong style="color:#721C24;font-size:1.1rem">
                                    {{ number_format($payment->amount,0,',',' ') }} XOF
                                </strong>
                            </div>
                        </div>
                        <p class="text-muted mb-0" style="font-size:.8rem">
                            Cette action lance un dépôt Mobile Money FedaPay vers le numéro du client.
                            Le paiement ne sera marqué comme remboursé qu'après confirmation du statut FedaPay.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-check2-circle me-1"></i>Confirmer le remboursement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="row g-4">
    {{-- Historique paiements --}}
    <div class="col-lg-8">
        <div class="content-card">
            <h5 class="fw-700 mb-3">Historique des transactions</h5>

            {{-- Tableau (desktop / tablette) --}}
            <div class="table-responsive desktop-only-table">
                <table class="table align-middle" style="font-size:.82rem">
                    <thead>
                        <tr style="border-bottom:2px solid #ECD8C6">
                            <th>#</th><th>Commande</th><th>Artisan</th>
                            <th>Montant</th><th>Commission</th><th>Net artisan</th>
                            <th>Reversé</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr style="border-bottom:1px solid #f0e8de;vertical-align:middle">
                                <td class="text-muted">#{{ $p->order->id }}</td>
                                <td>
                                    <div class="fw-600">{{ Str::limit($p->order->title,22) }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $p->order->client->name }}</div>
                                </td>
                                <td>{{ $p->order->artisan->name }}</td>
                                <td class="fw-600">{{ number_format($p->amount,0,',',' ') }}</td>
                                <td style="color:#C4622D;font-weight:600">
                                    {{ number_format($p->commission,0,',',' ') }}
                                    <span class="text-muted" style="font-weight:400">({{ round($p->commission_rate*100) }}%)</span>
                                </td>
                                <td style="color:#155724;font-weight:700">{{ number_format($p->net_amount,0,',',' ') }}</td>
                                <td>
                                    @if($p->isReversed())
                                        <span class="badge" style="background:#D4EDDA;color:#155724;font-size:.72rem">
                                            <i class="bi bi-check-lg me-1"></i>{{ $p->reversed_at->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="badge" style="background:#FFF3CD;color:#856404;font-size:.72rem">En attente</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $p->paid_at?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">Aucun paiement</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Cartes (mobile) --}}
            <div class="mobile-cards">
                @forelse($payments as $p)
                <div class="mcard">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="fw-700" style="font-size:.9rem">{{ Str::limit($p->order->title,22) }}</div>
                        <span class="text-muted" style="font-size:.75rem">#{{ $p->order->id }}</span>
                    </div>
                    <div class="mcard-row"><span class="label">Client</span><span>{{ $p->order->client->name }}</span></div>
                    <div class="mcard-row"><span class="label">Artisan</span><span>{{ $p->order->artisan->name }}</span></div>
                    <div class="mcard-row"><span class="label">Montant</span><span class="fw-600">{{ number_format($p->amount,0,',',' ') }} XOF</span></div>
                    <div class="mcard-row">
                        <span class="label">Commission</span>
                        <span style="color:#C4622D;font-weight:600">
                            {{ number_format($p->commission,0,',',' ') }} ({{ round($p->commission_rate*100) }}%)
                        </span>
                    </div>
                    <div class="mcard-row">
                        <span class="label">Net artisan</span>
                        <span style="color:#155724;font-weight:700">{{ number_format($p->net_amount,0,',',' ') }} XOF</span>
                    </div>
                    <div class="mcard-row">
                        <span class="label">Reversé</span>
                        @if($p->isReversed())
                            <span class="badge" style="background:#D4EDDA;color:#155724"><i class="bi bi-check-lg me-1"></i>{{ $p->reversed_at->format('d/m/Y') }}</span>
                        @else
                            <span class="badge" style="background:#FFF3CD;color:#856404">En attente</span>
                        @endif
                    </div>
                    <div class="mcard-row"><span class="label">Date</span><span>{{ $p->paid_at?->format('d/m/Y') ?? '—' }}</span></div>
                </div>
                @empty
                <p class="text-center text-muted py-4">Aucun paiement</p>
                @endforelse
            </div>

            <div class="mt-3">{{ $payments->links() }}</div>
        </div>
    </div>

    {{-- Top artisans --}}
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="fw-700 mb-3">🏆 Top artisans</h5>
            @forelse($topArtisans as $i => $artisan)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom flex-wrap"
                     style="border-color:#F5EFE6!important">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:28px;height:28px;
                                background:{{ $i===0?'#D4A853':($i===1?'#C0C0C0':($i===2?'#CD7F32':'#F5EFE6')) }};
                                color:{{ $i<3?'#fff':'#9A8070' }};font-weight:700;font-size:.8rem">
                        {{ $i+1 }}
                    </div>
                    <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle"
                         width="32" height="32" style="object-fit:cover">
                    <div class="flex-grow-1" style="min-width:120px">
                        <div class="fw-600" style="font-size:.85rem">{{ $artisan->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $artisan->artisanProfile?->specialty }}</div>
                    </div>
                    <div class="text-clay fw-700" style="font-size:.82rem;white-space:nowrap">
                        {{ $artisan->total_earned ? number_format($artisan->total_earned,0,',',' ').' XOF' : '—' }}
                    </div>
                </div>
            @empty
                <p class="text-center text-muted py-3">Aucun artisan payé</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
