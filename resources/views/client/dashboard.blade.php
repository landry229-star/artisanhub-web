@extends('layouts.dashboard')
@section('title', 'Espace Client')
@section('page-title', 'Mon Espace')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('client.dashboard') }}" class="sidebar-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('client.orders.index') }}" class="sidebar-link {{ request()->routeIs('client.orders.*') ? 'active' : '' }}">
        <i class="bi bi-bag"></i> Mes commandes
    </a>
    <a href="{{ route('client.wallet.index') }}" class="sidebar-link {{ request()->routeIs('client.wallet.*') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i> Mes paiements
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <div class="sidebar-section-title mt-2">Mon compte</div>
    <a href="{{ route('client.profile.edit') }}" class="sidebar-link {{ request()->routeIs('client.profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-gear"></i> Mon profil
    </a>
    <a href="{{ route('client.favorites.index') }}" class="sidebar-link">
        <i class="bi bi-heart"></i> Mes favoris
    </a>
    <a href="{{ route('client.support.index') }}" class="sidebar-link">
        <i class="bi bi-life-preserver"></i> Support
    </a>
    <div class="sidebar-section-title mt-2">Découvrir</div>
    <a href="{{ route('artisans.index') }}" class="sidebar-link">
        <i class="bi bi-search"></i> Explorer les artisans
    </a>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <p class="section-label mb-1">Bonjour</p>
        <h1 style="font-size:1.8rem">{{ $user->name }} 👋</h1>
        <p class="text-muted mb-0">{{ $user->city }}</p>
    </div>
    <a href="{{ route('artisans.index') }}" class="btn btn-clay">
        <i class="bi bi-search me-2"></i>Trouver un artisan
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php $statCards = [
        ['label'=>'Total commandes',  'value'=>$stats['total'],       'icon'=>'bi-bag',          'color'=>'#F5EFE6','icolor'=>'var(--clay)'],
        ['label'=>'En cours',         'value'=>$stats['in_progress'], 'icon'=>'bi-tools',        'color'=>'#CCE5FF','icolor'=>'#004085'],
        ['label'=>'Terminées',        'value'=>$stats['completed'],   'icon'=>'bi-check-circle', 'color'=>'#D4EDDA','icolor'=>'#155724'],
        ['label'=>'Dépenses XOF',     'value'=>number_format($stats['spent'],0,',',' '), 'icon'=>'bi-cash-coin','color'=>'#FFF3CD','icolor'=>'#856404'],
    ]; @endphp
    @foreach($statCards as $sc)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon mb-3" style="background:{{ $sc['color'] }}">
                    <i class="bi {{ $sc['icon'] }}" style="color:{{ $sc['icolor'] }}"></i>
                </div>
                <div class="stat-value">{{ $sc['value'] }}</div>
                <div class="stat-label">{{ $sc['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-700 mb-0">Commandes récentes</h5>
                <a href="{{ route('client.orders.index') }}" class="btn btn-sm btn-outline-clay">Voir tout</a>
            </div>
            @forelse($recentOrders as $order)
                <div class="d-flex align-items-center gap-3 py-3 border-bottom" style="border-color:#F5EFE6!important">
                    <img src="{{ $order->artisan->avatarUrl() }}" class="rounded-circle"
                         width="40" height="40" style="object-fit:cover;flex-shrink:0">
                    <div class="flex-grow-1">
                        <div class="fw-600">{{ $order->title }}</div>
                        <div class="text-muted" style="font-size:.82rem">
                            {{ $order->artisan->name }} · {{ $order->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                        <a href="{{ route('client.orders.show', $order) }}" class="btn btn-sm btn-outline-clay">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                    <p class="mb-2">Aucune commande pour le moment</p>
                    <a href="{{ route('artisans.index') }}" class="btn btn-clay btn-sm">
                        <i class="bi bi-search me-1"></i>Trouver un artisan
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-4">
        <div class="content-card" style="background:linear-gradient(135deg,#2C1A0E,#9E4A1E);color:#fff">
            <p style="color:#D4A853;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em" class="mb-2">BESOIN D'UN ARTISAN ?</p>
            <h5 class="fw-700 mb-2" style="color:#fff">Trouvez le bon profil</h5>
            <p style="color:#C8B5A0;font-size:.875rem" class="mb-3">
                Plus de 1 200 artisans vérifiés disponibles dans toutes les villes du Bénin.
            </p>
            <a href="{{ route('artisans.index') }}" class="btn btn-clay w-100">
                <i class="bi bi-search me-2"></i>Explorer
            </a>
        </div>
    </div>
</div>
@endsection
