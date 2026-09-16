@extends('layouts.dashboard')
@section('title', 'Admin Dashboard')
@section('page-title', 'Administration')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('content')
<div class="mb-4">
    <p class="section-label mb-1">ADMINISTRATION</p>
    <h1 style="font-size:1.8rem">Tableau de bord 🛡️</h1>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    @php $kpis = [
        ['label'=>'Artisans',        'value'=>$stats['total_artisans'],   'icon'=>'bi-tools',         'color'=>'#F5EFE6','icolor'=>'#C4622D'],
        ['label'=>'Clients',         'value'=>$stats['total_clients'],    'icon'=>'bi-people',        'color'=>'#CCE5FF','icolor'=>'#004085'],
        ['label'=>'Cmd. en attente', 'value'=>$stats['pending_orders'],   'icon'=>'bi-clock',         'color'=>'#FFF3CD','icolor'=>'#856404'],
        ['label'=>'Litiges actifs',  'value'=>$stats['disputed_orders'],  'icon'=>'bi-exclamation-triangle','color'=>'#F8D7DA','icolor'=>'#721C24'],
        ['label'=>'Cmd. terminées',  'value'=>$stats['completed_orders'], 'icon'=>'bi-check-circle',  'color'=>'#D4EDDA','icolor'=>'#155724'],
        ['label'=>'Revenus XOF',     'value'=>number_format($stats['total_revenue'],0,',',' '), 'icon'=>'bi-cash-coin','color'=>'#F5EFE6','icolor'=>'#C4622D'],
        ['label'=>'À vérifier',      'value'=>$stats['unverified'],       'icon'=>'bi-shield-exclamation','color'=>'#FFF3CD','icolor'=>'#856404'],
    ]; @endphp
    @foreach($kpis as $k)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon mb-3" style="background:{{ $k['color'] }}">
                    <i class="bi {{ $k['icon'] }}" style="color:{{ $k['icolor'] }}"></i>
                </div>
                <div class="stat-value">{{ $k['value'] }}</div>
                <div class="stat-label">{{ $k['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    {{-- Commandes urgentes --}}
    <div class="col-lg-7">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-700 mb-0">Commandes urgentes</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-clay">Tout voir</a>
            </div>
            @forelse($recentOrders as $order)
                <div class="d-flex gap-3 align-items-center py-2 border-bottom flex-wrap" style="border-color:#F5EFE6!important">
                    <div class="flex-grow-1" style="min-width:160px">
                        <div class="fw-600" style="font-size:.9rem">{{ Str::limit($order->title, 40) }}</div>
                        <div class="text-muted" style="font-size:.78rem">
                            {{ $order->client->name }} → {{ $order->artisan->name }}
                            · {{ $order->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                    <a href="{{ route('admin.orders.index') }}?status={{ $order->status }}"
                       class="btn btn-sm btn-outline-clay py-0 px-2">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            @empty
                <p class="text-center text-muted py-3">Aucune commande urgente ✅</p>
            @endforelse
        </div>
    </div>

    {{-- Nouveaux users --}}
    <div class="col-lg-5">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-700 mb-0">Nouveaux inscrits</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-clay">Tout voir</a>
            </div>
            @foreach($recentUsers as $user)
                <div class="d-flex gap-2 align-items-center py-2 border-bottom flex-wrap" style="border-color:#F5EFE6!important">
                    <img src="{{ $user->avatarUrl() }}" class="rounded-circle"
                         width="36" height="36" style="object-fit:cover">
                    <div class="flex-grow-1" style="min-width:120px">
                        <div class="fw-600" style="font-size:.875rem">{{ $user->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ ucfirst($user->role) }} · {{ $user->city }}</div>
                    </div>
                    @if($user->role === 'artisan' && !$user->is_verified)
                        <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm" style="background:#D4EDDA;color:#155724;font-size:.75rem">
                                Vérifier
                            </button>
                        </form>
                    @else
                        <span class="badge" style="background:{{ $user->is_active ? '#D4EDDA' : '#F8D7DA' }};color:{{ $user->is_active ? '#155724' : '#721C24' }};font-size:.72rem">
                            {{ $user->is_active ? 'Actif' : 'Suspendu' }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>


<div class="mb-3">
    <p class="section-label mb-1">ANALYSE</p>
    <h5 class="fw-700 mb-0">Statistiques avancées</h5>
</div>

@isset($advancedStats)
    {{-- KPIs secondaires --}}
    <div class="row g-3 mb-4">
        @php $advKpis = [
            ['label'=>'Panier moyen',       'value'=>number_format($advancedStats['avg_basket'],0,',',' ').' XOF', 'icon'=>'bi-basket',       'bg'=>'#F5EFE6','ic'=>'#C4622D'],
            ['label'=>'Taux de conversion', 'value'=>$advancedStats['conversion_rate'].'%',                        'icon'=>'bi-graph-up-arrow','bg'=>'#D4EDDA','ic'=>'#155724'],
            ['label'=>'Nouveaux clients (7j)',  'value'=>$advancedStats['new_clients_week'],                       'icon'=>'bi-person-plus',  'bg'=>'#CCE5FF','ic'=>'#004085'],
            ['label'=>'Nouveaux artisans (7j)', 'value'=>$advancedStats['new_artisans_week'],                      'icon'=>'bi-tools',        'bg'=>'#FFF3CD','ic'=>'#856404'],
        ]; @endphp
        @foreach($advKpis as $k)
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon mb-2" style="background:{{ $k['bg'] }}">
                        <i class="bi {{ $k['icon'] }}" style="color:{{ $k['ic'] }}"></i>
                    </div>
                    <div class="stat-value" style="font-size:1.05rem">{{ $k['value'] }}</div>
                    <div class="stat-label">{{ $k['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="content-card">
                <h6 class="fw-700 mb-3">Évolution des revenus (30 derniers jours)</h6>
                <div style="position:relative;height:260px">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="content-card">
                <h6 class="fw-700 mb-3">Répartition des commandes</h6>
                <div style="position:relative;height:260px">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="content-card text-center py-3">
                <i class="bi bi-geo-alt-fill fs-4 mb-1" style="color:#C4622D"></i>
                <p class="text-muted mb-0" style="font-size:.75rem">Ville la plus active</p>
                <p class="fw-700 mb-0">{{ $advancedStats['top_city'] }}</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="content-card text-center py-3">
                <i class="bi bi-star-fill fs-4 mb-1" style="color:#C4622D"></i>
                <p class="text-muted mb-0" style="font-size:.75rem">Spécialité la + demandée</p>
                <p class="fw-700 mb-0">{{ $advancedStats['top_specialty'] }}</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="content-card text-center py-3">
                <i class="bi bi-check2-circle fs-4 mb-1" style="color:#155724"></i>
                <p class="text-muted mb-0" style="font-size:.75rem">Taux d’acceptation</p>
                <p class="fw-700 mb-0">{{ $advancedStats['acceptance_rate'] }}%</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="content-card text-center py-3">
                <i class="bi bi-clock-history fs-4 mb-1" style="color:#004085"></i>
                <p class="text-muted mb-0" style="font-size:.75rem">Temps moyen livraison</p>
                <p class="fw-700 mb-0">{{ $advancedStats['avg_delivery_minutes'] }} min</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="content-card h-100">
                <h6 class="fw-700 mb-3">Revenus par ville</h6>
                @forelse($advancedStats['revenue_by_city'] as $row)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>{{ $row->city }}</span>
                        <strong>{{ number_format((float) $row->revenue, 0, ',', ' ') }} XOF</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune donnée</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card h-100">
                <h6 class="fw-700 mb-3">Meilleurs artisans</h6>
                @forelse($advancedStats['revenue_by_artisan'] as $row)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>{{ $row->artisan }}</span>
                        <strong>{{ number_format((float) $row->revenue, 0, ',', ' ') }} XOF</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune donnée</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card h-100">
                <h6 class="fw-700 mb-3">Services les plus rentables</h6>
                @forelse($advancedStats['revenue_by_service'] as $row)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>{{ Str::limit($row->service, 26) }}</span>
                        <strong>{{ number_format((float) $row->revenue, 0, ',', ' ') }} XOF</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="content-card h-100">
                <h6 class="fw-700 mb-2">Litiges</h6>
                <div class="display-6 fw-700">{{ $advancedStats['dispute_rate'] }}%</div>
                <p class="text-muted mb-0">Part des commandes concernées par un litige.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card h-100">
                <h6 class="fw-700 mb-2">Remboursements</h6>
                <div class="display-6 fw-700">{{ $advancedStats['refund_rate'] }}%</div>
                <p class="text-muted mb-0">Paiements avec remboursement ou demande de remboursement.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card h-100">
                <h6 class="fw-700 mb-2">Exports récents</h6>
                @forelse($recentExports as $export)
                    <div class="small text-muted py-1">{{ $export->resource }} · {{ $export->format }} · {{ $export->created_at->format('d/m/Y H:i') }}</div>
                @empty
                    <p class="text-muted mb-0">Aucun export</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: @json($advancedStats['revenue_labels']),
                        datasets: [{
                            label: 'Revenus (XOF)',
                            data: @json($advancedStats['revenue_values']),
                            borderColor: '#C4622D',
                            backgroundColor: 'rgba(196,98,45,.12)',
                            tension: .35,
                            fill: true,
                            pointRadius: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } },
                            y: { ticks: { callback: v => new Intl.NumberFormat('fr-FR').format(v) } }
                        }
                    }
                });
            }

            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($advancedStats['status_labels']),
                        datasets: [{
                            data: @json($advancedStats['status_values']),
                            backgroundColor: ['#FFF3CD','#CCE5FF','#F5EFE6','#D4EDDA','#F8D7DA']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
                    }
                });
            }
        });
    </script>
    @endpush
@else
    <div class="content-card text-center py-4 mb-4">
        <i class="bi bi-bar-chart-line fs-3 text-muted mb-2 d-block"></i>
        <p class="text-muted mb-0" style="font-size:.9rem">
            Les statistiques avancées ne sont pas encore disponibles.<br>
            Le contrôleur doit fournir une variable <code>$advancedStats</code>
            (voir le commentaire dans <code>dashboard.blade.php</code> pour la structure attendue).
        </p>
    </div>
@endisset

@endsection
