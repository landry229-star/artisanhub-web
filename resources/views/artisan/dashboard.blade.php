@extends('layouts.dashboard')
@section('title', 'Espace Artisan')
@section('page-title', 'Mon Espace')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('topbar-actions')
    <form action="{{ route('artisan.profile.availability') }}" method="POST">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm {{ $profile->is_available ? 'btn-success' : 'btn-danger' }}">
            <i class="bi bi-circle-fill me-1" style="font-size:.6rem"></i>
            {{ $profile->is_available ? 'Disponible' : 'Occupé' }}
        </button>
    </form>
@endsection

@section('content')

{{-- Bienvenue --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div style="min-width:0">
        <p class="section-label mb-1">Bonjour</p>
        <h1 style="font-size:clamp(1.3rem,5vw,1.8rem);word-break:break-word">{{ $user->name }} 👋</h1>
        <p class="text-muted mb-0">{{ $profile->specialty }} · {{ $user->city }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap" style="align-self:flex-start">
        <span class="badge px-3 py-2 badge-{{ strtolower(str_replace(' ','',($profile->badge()))) }}"
              style="background:{{ $profile->badge()==='Top Artisan' ? 'var(--clay)' : ($profile->badge()==='Vérifié' ? '#2E7D32' : 'var(--gold)') }};color:#fff;font-size:.8rem;white-space:nowrap">
            {{ $profile->badge() }}
        </span>
        <span class="badge px-3 py-2" style="background:{{ $profile->tierColor() }};color:#fff;font-size:.8rem;white-space:nowrap">
            <i class="bi bi-award me-1"></i>{{ $profile->tierLabel() }}
        </span>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
        $statCards = [
            ['label'=>'En attente',   'value'=>$stats['pending'],     'icon'=>'bi-clock',        'color'=>'#FFF3CD','icolor'=>'#856404'],
            ['label'=>'En cours',     'value'=>$stats['in_progress'], 'icon'=>'bi-tools',        'color'=>'#CCE5FF','icolor'=>'#004085'],
            ['label'=>'Terminées',    'value'=>$stats['completed'],   'icon'=>'bi-check-circle', 'color'=>'#D4EDDA','icolor'=>'#155724'],
            ['label'=>'Revenus XOF',  'value'=>number_format($stats['revenue'],0,',',' '), 'icon'=>'bi-cash-coin','color'=>'#F5EFE6','icolor'=>'var(--clay)'],
        ];
    @endphp
    @foreach($statCards as $sc)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon" style="background:{{ $sc['color'] }}">
                        <i class="bi {{ $sc['icon'] }}" style="color:{{ $sc['icolor'] }}"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $sc['value'] }}</div>
                <div class="stat-label">{{ $sc['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    {{-- Commandes récentes --}}
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-700">Commandes récentes</h5>
                <a href="{{ route('artisan.orders.index') }}" class="btn btn-sm btn-outline-clay">Voir tout</a>
            </div>
            @forelse($recentOrders as $order)
                <div class="d-flex align-items-center gap-3 py-3 border-bottom" style="border-color:#F5EFE6!important">
                    <img src="{{ $order->client->avatarUrl() }}" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                    <div class="flex-grow-1">
                        <div class="fw-600">{{ $order->title }}</div>
                        <div class="text-muted" style="font-size:.82rem">
                            {{ $order->client->name }} · {{ $order->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                        @if($order->budget)
                            <span class="text-clay fw-700" style="font-size:.85rem;white-space:nowrap">
                                {{ number_format($order->budget,0,',',' ') }} XOF
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-4 d-block mb-2" style="opacity:.3"></i>
                    Aucune commande pour le moment
                </div>
            @endforelse
        </div>
    </div>

    {{-- Profil rapide --}}
    <div class="col-lg-4">
        <div class="content-card text-center">
            <img src="{{ $user->avatarUrl() }}" class="rounded-circle mb-3"
                 width="80" height="80" style="object-fit:cover;border:3px solid var(--clay)">
            <h6 class="fw-700 mb-0">{{ $user->name }}</h6>
            <p class="text-muted mb-2" style="font-size:.85rem">{{ $profile->specialty }}</p>

            @if($profile->rating > 0)
                <div class="stars mb-1">
                    @for($i=1;$i<=5;$i++)
                        <i class="bi bi-star{{ $i <= round($profile->rating) ? '-fill' : '' }}"></i>
                    @endfor
                </div>
                <p class="text-muted mb-3" style="font-size:.82rem">
                    {{ $profile->rating }}/5 · {{ $profile->reviews_count }} avis
                </p>
            @else
                <p class="text-muted mb-3" style="font-size:.82rem">Pas encore d'avis</p>
            @endif

            <a href="{{ route('artisan.profile.edit') }}" class="btn btn-clay btn-sm w-100 mb-2">
                <i class="bi bi-pencil me-1"></i>Modifier mon profil
            </a>
            <a href="{{ route('artisan.portfolio.index') }}" class="btn btn-outline-clay btn-sm w-100">
                <i class="bi bi-images me-1"></i>Gérer le portfolio
            </a>
        </div>

        @if($profile->portfolioItems->count() === 0)
            <div class="content-card mt-3" style="background:var(--sand);border-color:var(--clay-light)">
                <p class="section-label mb-1">💡 Conseil</p>
                <p class="mb-2" style="font-size:.875rem">
                    Ajoutez des photos de vos réalisations pour attirer plus de clients.
                </p>
                <a href="{{ route('artisan.portfolio.index') }}" class="btn btn-clay btn-sm">
                    Ajouter des photos
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
