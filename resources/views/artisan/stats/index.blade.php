@extends('layouts.dashboard')
@section('title', 'Mes statistiques')
@section('page-title', 'Mes statistiques')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total gagné',       'value'=>number_format($stats['total_earned'],0,',',' ').' XOF', 'icon'=>'bi-cash-coin',    'bg'=>'#D4EDDA','ic'=>'#155724'],
        ['label'=>'Ce mois',           'value'=>number_format($stats['month_earned'],0,',',' ').' XOF', 'icon'=>'bi-calendar-month','bg'=>'#F5EFE6','ic'=>'#C4622D'],
        ['label'=>'Commandes terminées','value'=>$stats['completed_orders'],                             'icon'=>'bi-check-circle', 'bg'=>'#CCE5FF','ic'=>'#004085'],
        ['label'=>"Taux d'acceptation",'value'=>$stats['accept_rate'].'%',                              'icon'=>'bi-percent',      'bg'=>'#FFF3CD','ic'=>'#856404'],
    ] as $k)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon mb-3" style="background:{{ $k['bg'] }}">
                    <i class="bi {{ $k['icon'] }}" style="color:{{ $k['ic'] }}"></i>
                </div>
                <div class="stat-value" style="font-size:1.3rem">{{ $k['value'] }}</div>
                <div class="stat-label">{{ $k['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    {{-- Graphique revenus 12 mois --}}
    <div class="col-lg-8">
        <div class="content-card">
            <h5 class="fw-700 mb-4">Revenus sur 12 mois</h5>
            @php $max = max(collect($monthly)->pluck('earned')->toArray() ?: [1]); @endphp
            {{-- Bug corrigé : 12 barres tassées dans la largeur de l'écran devenaient
                 illisibles sur mobile (~27px/barre, libellés qui se chevauchent).
                 On garde une largeur minimale par barre et on laisse défiler
                 horizontalement sur petit écran plutôt que tout écraser. --}}
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                <div class="d-flex align-items-end gap-1 chart-12m" style="height:160px;min-width:480px">
                    @foreach($monthly as $m)
                        @php $pct = $max > 0 ? max(round(($m['earned']/$max)*100), 2) : 2; @endphp
                        <div class="d-flex flex-column align-items-center gap-1" style="flex:1;min-width:36px">
                            @if($m['earned'] > 0)
                                <div style="font-size:.65rem;color:#C4622D;font-weight:600;white-space:nowrap">
                                    {{ number_format($m['earned']/1000,0) }}k
                                </div>
                            @else
                                <div style="font-size:.65rem">&nbsp;</div>
                            @endif
                            <div class="rounded-top w-100"
                                 style="height:{{ $pct }}%;background:{{ $m['earned']>0?'#C4622D':'#ECD8C6' }};
                                        min-height:4px;transition:.3s"
                                 title="{{ $m['label'] }} : {{ number_format($m['earned'],0,',',' ') }} XOF">
                            </div>
                            <div style="font-size:.65rem;color:#9A8070;white-space:nowrap;transform:rotate(-30deg);transform-origin:top left;margin-top:4px">
                                {{ explode(' ', $m['label'])[0] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <p class="text-muted mt-2 mb-0 d-lg-none" style="font-size:.72rem">
                <i class="bi bi-arrow-left-right me-1"></i>Faites glisser pour voir tous les mois
            </p>
        </div>
    </div>

    {{-- Distribution des notes --}}
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="fw-700 mb-3">Distribution des avis</h5>
            @php $totalReviews = $ratingDist->sum(); @endphp
            @foreach([5,4,3,2,1] as $star)
                @php
                    $count = $ratingDist[$star] ?? 0;
                    $pct   = $totalReviews > 0 ? round(($count/$totalReviews)*100) : 0;
                @endphp
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span style="font-size:.8rem;color:#D4A853;width:60px;white-space:nowrap">
                        {{ $star }} ★
                    </span>
                    <div class="flex-grow-1 rounded-pill" style="background:#ECD8C6;height:8px">
                        <div class="rounded-pill" style="background:#D4A853;height:8px;width:{{ $pct }}%;transition:.3s"></div>
                    </div>
                    <span class="text-muted" style="font-size:.78rem;width:24px;text-align:right">
                        {{ $count }}
                    </span>
                </div>
            @endforeach
            @if($stats['reviews_count'] > 0)
                <div class="text-center mt-3 p-2 rounded" style="background:#F5EFE6">
                    <div style="font-size:1.8rem;font-weight:700;color:#C4622D">
                        {{ $stats['avg_rating'] }}/5
                    </div>
                    <div class="text-muted" style="font-size:.8rem">
                        {{ $stats['reviews_count'] }} avis
                    </div>
                </div>
            @else
                <p class="text-center text-muted mt-3" style="font-size:.85rem">
                    Aucun avis pour le moment
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
