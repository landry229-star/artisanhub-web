@extends('layouts.dashboard')
@section('title', 'Mon Solde')
@section('page-title', 'Mon Solde')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon mb-3" style="background:#D4EDDA">
                <i class="bi bi-cash-coin" style="color:#155724"></i>
            </div>

            <div class="mb-4 content-card">
                <div class="flex-wrap gap-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-700">Documents financiers</h5>
                        <p class="mb-0 text-muted" style="font-size:.85rem">
                            Téléchargez votre relevé et les justificatifs de paiements reçus.
                        </p>
                    </div>
                    <a href="{{ route('artisan.wallet.statement') }}" class="btn btn-sm btn-clay">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Relevé financier PDF
                    </a>
                </div>
            </div>
            <div class="stat-value">{{ number_format($stats['total_earned'],0,',',' ') }}</div>
            <div class="stat-label">Total gagné (XOF)</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon mb-3" style="background:#F5EFE6">
                <i class="bi bi-calendar-month" style="color:#C4622D"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_month'],0,',',' ') }}</div>
            <div class="stat-label">Ce mois-ci (XOF)</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon mb-3" style="background:#FFF3CD">
                <i class="bi bi-hourglass-split" style="color:#856404"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['pending_amount'],0,',',' ') }}</div>
            <div class="stat-label">En attente (XOF)</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon mb-3" style="background:#CCE5FF">
                <i class="bi bi-percent" style="color:#004085"></i>
            </div>
            <div class="stat-value">{{ round($stats['commission_rate']*100) }}%</div>
            <div class="stat-label">
                Taux commission
                @php
                    $rate = $stats['commission_rate'];
                @endphp
                <span class="d-block mt-1 badge"
                      style="background:{{ $rate==0.05?'#D4EDDA':($rate==0.08?'#FFF3CD':'#F8D7DA') }};
                             color:{{ $rate==0.05?'#155724':($rate==0.08?'#856404':'#721C24') }};
                             font-size:.7rem">
                    {{ $rate==0.05?'Débutant':($rate==0.08?'Actif':'Top') }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Info commission --}}
<div class="content-card mb-4" style="background:#F5EFE6;border-color:#ECD8C6">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div>
            <p class="section-label mb-1">COMMENT FONCTIONNE LA COMMISSION</p>
            <p class="mb-0" style="font-size:.85rem;color:#5C3D1E">
                La commission est calculée sur votre chiffre d'affaires du mois en cours.
                Plus vous gagnez, plus elle augmente progressivement.
            </p>
        </div>
        <div class="d-flex gap-3 ms-auto flex-wrap">
            @foreach([['0–50k','5%','#D4EDDA','#155724'],['50k–200k','8%','#FFF3CD','#856404'],['200k+','10%','#F8D7DA','#721C24']] as [$range,$pct,$bg,$col])
                <div class="text-center px-3 py-2 rounded-3" style="background:{{ $bg }}">
                    <div style="color:{{ $col }};font-weight:700;font-size:1.1rem">{{ $pct }}</div>
                    <div style="color:{{ $col }};font-size:.75rem">{{ $range }} XOF/mois</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Graphique évolution --}}
    <div class="col-lg-8">
        <div class="content-card mb-4">
            <h5 class="fw-700 mb-4">Évolution des 6 derniers mois</h5>
            @php $max = max(collect($monthly)->pluck('amount')->toArray() ?: [1]); @endphp
            <div class="d-flex align-items-end gap-2" style="height:140px">
                @foreach($monthly as $m)
                    @php $pct = $max > 0 ? round(($m['amount']/$max)*100) : 0; @endphp
                    <div class="flex-1 d-flex flex-column align-items-center gap-1" style="flex:1">
                        <div style="font-size:.72rem;color:#C4622D;font-weight:600;white-space:nowrap">
                            @if($m['amount']>0)
                                {{ number_format($m['amount']/1000,0) }}k
                            @endif
                        </div>
                        <div class="rounded-top" style="width:100%;
                             height:{{ max($pct,4) }}%;
                             background:{{ $m['amount']>0 ? '#C4622D' : '#ECD8C6' }};
                             min-height:4px;transition:.3s">
                        </div>
                        <div style="font-size:.7rem;color:#9A8070;white-space:nowrap">{{ $m['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Historique paiements --}}
        <div class="content-card">
            <h5 class="fw-700 mb-3">Historique des paiements reçus</h5>
            @forelse($payments as $p)
                <div class="d-flex align-items-center gap-3 py-3 border-bottom flex-wrap"
                     style="border-color:#F5EFE6!important">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:#D4EDDA">
                        <i class="bi bi-check-circle" style="color:#155724"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:140px">
                        <div class="fw-600" style="font-size:.9rem">
                            {{ $p->order->title }}
                        </div>
                        <div class="text-muted" style="font-size:.78rem">
                            Client : {{ $p->order->client->name }}
                            · {{ $p->paid_at ? $p->paid_at->format('d/m/Y') : '—' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-700 text-clay">
                            +{{ number_format($p->net_amount,0,',',' ') }} XOF
                        </div>
                        <div class="text-muted" style="font-size:.75rem">
                            Commission {{ $p->commissionLabel() }}
                        </div>
                        <a href="{{ route('artisan.wallet.receipt', $p) }}" class="small text-clay">
                            Justificatif PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-wallet2" style="font-size:2rem;opacity:.25;display:block;margin-bottom:.5rem"></i>
                    Aucun paiement reçu pour le moment
                </div>
            @endforelse
        </div>
    </div>

    {{-- Commandes en attente --}}
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="fw-700 mb-3">
                <i class="bi bi-hourglass-split me-2 text-clay"></i>À recevoir
            </h5>
            @forelse($pendingOrders as $order)
                <div class="py-3 border-bottom" style="border-color:#F5EFE6!important">
                    <div class="fw-600" style="font-size:.875rem">{{ Str::limit($order->title,35) }}</div>
                    <div class="text-muted" style="font-size:.78rem">{{ $order->client->name }}</div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="badge-status status-{{ $order->status }}">
                            {{ $order->statusLabel() }}
                        </span>
                        @if($order->budget)
                            {{-- Bug corrigé : "Payment" n'était pas importé dans ce fichier Blade
                                 (contrairement au contrôleur), ce qui provoquait une erreur
                                 "Class not found" à l'affichage de cette page. --}}
                            @php $net = $order->budget * (1 - \App\Models\Payment::getCommissionRate(auth()->id())); @endphp
                            <span class="fw-700 text-clay" style="font-size:.85rem">
                                ~{{ number_format($net,0,',',' ') }} XOF
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-center text-muted py-3" style="font-size:.85rem">
                    Aucune commande en attente
                </p>
            @endforelse
        </div>

        {{-- Info virement --}}
        <div class="content-card mt-3" style="background:#F5EFE6">
            <p class="section-label mb-2">💰 VERSEMENTS</p>
            <p style="font-size:.82rem;color:#5C3D1E;margin-bottom:0">
                Les paiements sont versés sur votre Mobile Money
                <strong>24 à 48h ouvrées</strong> après la validation du client.
            </p>
        </div>
    </div>

</div>

@push('styles')
<style>
    @media (max-width: 575.98px) {
        /* Pastilles de paliers de commission : passent en grille 3 colonnes égales
           au lieu de se compresser horizontalement */
        .content-card .d-flex.gap-3.ms-auto.flex-wrap {
            width: 100%;
            justify-content: space-between;
            margin-left: 0 !important;
        }
        .content-card .d-flex.gap-3.ms-auto.flex-wrap > div { flex: 1 1 0; }

        /* Graphique 6 mois : libellés plus compacts */
        [style*="height:140px"] > div > div:last-child { font-size: .62rem !important; }
    }
</style>
@endpush
@endsection
