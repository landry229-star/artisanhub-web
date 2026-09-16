@extends('layouts.dashboard')
@section('title', 'Espace Livreur')
@section('page-title', 'Mon Espace Livreur')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('livreur.dashboard') }}" class="sidebar-link active">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('livreur.missions.index') }}" class="sidebar-link">
        <i class="bi bi-bicycle"></i> Mes missions
    </a>
    <a href="{{ route('livreur.wallet.index') }}" class="sidebar-link">
        <i class="bi bi-wallet2"></i> Mon solde
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <div class="sidebar-section-title mt-2">Mon compte</div>
    <a href="{{ route('livreur.profile.edit') }}" class="sidebar-link">
        <i class="bi bi-person-gear"></i> Mon profil
    </a>
@endsection

@section('topbar-actions')
    <form action="{{ route('livreur.availability') }}" method="POST">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm {{ auth()->user()->is_livreur_available ? 'btn-success' : 'btn-danger' }}">
            <i class="bi bi-circle-fill me-1" style="font-size:.6rem"></i>
            {{ auth()->user()->is_livreur_available ? 'Disponible' : 'Indisponible' }}
        </button>
    </form>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <p class="section-label mb-1">BONJOUR</p>
        <h1 style="font-size:1.8rem">{{ $user->name }} 🚴</h1>
        <p class="text-muted mb-0">Livreur · {{ $user->city }}</p>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Nouvelles missions', 'value'=>$stats['pending'],   'icon'=>'bi-bell',        'bg'=>'#FFF3CD','ic'=>'#856404'],
        ['label'=>'En cours',           'value'=>$stats['active'],    'icon'=>'bi-bicycle',     'bg'=>'#CCE5FF','ic'=>'#004085'],
        ['label'=>'Terminées',          'value'=>$stats['completed'], 'icon'=>'bi-check-circle','bg'=>'#D4EDDA','ic'=>'#155724'],
        ['label'=>'Gains XOF',          'value'=>number_format($stats['earned'],0,',',' '), 'icon'=>'bi-cash-coin','bg'=>'#F5EFE6','ic'=>'#C4622D'],
    ] as $k)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon mb-3" style="background:{{ $k['bg'] }}">
                    <i class="bi {{ $k['icon'] }}" style="color:{{ $k['ic'] }}"></i>
                </div>
                <div class="stat-value">{{ $k['value'] }}</div>
                <div class="stat-label">{{ $k['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- Missions actives --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card">
            <h5 class="fw-700 mb-3">Missions en cours</h5>
            @forelse($missions as $delivery)
                <div class="border rounded-3 p-3 mb-3" style="border-color:#ECD8C6!important">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-700">{{ $delivery->order->title }}</div>
                            <div class="text-muted" style="font-size:.82rem">
                                📍 {{ $delivery->pickup_city }} → {{ $delivery->delivery_city }}
                            </div>
                            <div class="text-muted" style="font-size:.82rem">
                                Client : {{ $delivery->order->client->name }}
                                · Artisan : {{ $delivery->order->artisan->name }}
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge px-2 py-1 rounded-pill"
                                  style="background:{{ $delivery->statusColor() }};
                                         color:{{ $delivery->statusTextColor() }};font-size:.78rem">
                                {{ $delivery->statusLabel() }}
                            </span>
                            <div class="fw-700 text-clay mt-1" style="font-size:.85rem">
                                {{ number_format($delivery->fee,0,',',' ') }} XOF
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <a href="{{ route('livreur.missions.show', $delivery) }}"
                           class="btn btn-sm btn-outline-clay">
                            <i class="bi bi-eye me-1"></i>Voir
                        </a>
                        @if($delivery->canBeAccepted())
                            <form action="{{ route('livreur.missions.accept', $delivery) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-clay">
                                    <i class="bi bi-check-lg me-1"></i>Accepter
                                </button>
                            </form>
                            <form action="{{ route('livreur.missions.refuse', $delivery) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Refuser cette mission ?')">
                                    <i class="bi bi-x-lg me-1"></i>Refuser
                                </button>
                            </form>
                        @endif
                        @if($delivery->status === 'acceptee')
                            <form action="{{ route('livreur.missions.enroute', $delivery) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-bicycle me-1"></i>En route vers l'artisan
                                </button>
                            </form>
                        @endif
                        @if($delivery->status === 'en_route')
                            <form action="{{ route('livreur.missions.pickup', $delivery) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="bi bi-box-seam me-1"></i>Objet récupéré
                                </button>
                            </form>
                        @endif
                        @if($delivery->status === 'recuperee')
                            <form action="{{ route('livreur.missions.deliver', $delivery) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success"
                                        onclick="return confirm('Confirmer la livraison chez le client ?')">
                                    <i class="bi bi-house-check me-1"></i>Livré chez le client
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-bicycle" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:.5rem"></i>
                    Aucune mission en cours
                </div>
            @endforelse
        </div>
    </div>

    {{-- Historique --}}
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="fw-700 mb-3">Historique récent</h5>
            @forelse($history as $d)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                     style="border-color:#F5EFE6!important">
                    <div>
                        <div class="fw-600" style="font-size:.85rem">{{ Str::limit($d->order->title,28) }}</div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $d->delivered_at ? $d->delivered_at->format('d/m/Y') : '—' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge px-2 py-1"
                              style="background:{{ $d->statusColor() }};color:{{ $d->statusTextColor() }};font-size:.72rem">
                            {{ $d->statusLabel() }}
                        </span>
                        <div class="fw-700 text-clay" style="font-size:.82rem">
                            {{ number_format($d->fee,0,',',' ') }} XOF
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted py-3" style="font-size:.85rem">Aucun historique</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
