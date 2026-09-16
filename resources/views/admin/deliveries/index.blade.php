@extends('layouts.dashboard')
@section('title', 'Livraisons')
@section('page-title', 'Gestion des livraisons')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('content')

<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Sans livreur',   'value'=>$stats['en_recherche'], 'bg'=>'#FFF3CD','ic'=>'#856404'],
        ['label'=>'En cours',       'value'=>$stats['active'],       'bg'=>'#CCE5FF','ic'=>'#004085'],
        ['label'=>'Livrées',        'value'=>$stats['livrees'],      'bg'=>'#D4EDDA','ic'=>'#155724'],
        ['label'=>'Échouées',       'value'=>$stats['echouees'],     'bg'=>'#F8D7DA','ic'=>'#721C24'],
    ] as $k)
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon mb-2" style="background:{{ $k['bg'] }}">
                    <i class="bi bi-bicycle" style="color:{{ $k['ic'] }}"></i>
                </div>
                <div class="stat-value">{{ $k['value'] }}</div>
                <div class="stat-label">{{ $k['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card">
    <form method="GET" class="row g-2 mb-4">
        <div class="col-12 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                @foreach(['en_recherche'=>'En recherche','assignee'=>'Assignée','acceptee'=>'Acceptée','en_route'=>'En route','recuperee'=>'Objet récupéré','livree'=>'Livrée','echouee'=>'Échouée'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3">
            <select name="city" class="form-select" onchange="this.form.submit()">
                <option value="">Toutes les villes</option>
                @foreach(config('artisanhub.cities_by_dept') as $dept => $villes)
                    <optgroup label="{{ $dept }}">
                        @foreach($villes as $ville)
                            <option value="{{ $ville }}" {{ request('city')===$ville?'selected':'' }}>{{ $ville }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th><th>Commande</th><th>Trajet</th>
                    <th>Livreur</th><th>Frais</th><th>Statut</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $d)
                    <tr>
                        <td class="text-muted" style="font-size:.8rem">#{{ $d->id }}</td>
                        <td>
                            <div class="fw-600" style="font-size:.85rem">{{ Str::limit($d->order->title,30) }}</div>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $d->order->client->name }} ← {{ $d->order->artisan->name }}
                            </div>
                        </td>
                        <td style="font-size:.82rem">
                            <i class="bi bi-geo-alt me-1 text-clay"></i>{{ $d->pickup_city }}<br>
                            <i class="bi bi-geo-alt-fill me-1" style="color:#155724"></i>{{ $d->delivery_city }}
                        </td>
                        <td>
                            @if($d->livreur)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $d->livreur->avatarUrl() }}" class="rounded-circle"
                                         width="28" height="28" style="object-fit:cover">
                                    <span style="font-size:.82rem">{{ $d->livreur->name }}</span>
                                </div>
                            @else
                                <span class="text-muted" style="font-size:.82rem">—</span>
                            @endif
                        </td>
                        <td class="fw-700 text-clay" style="font-size:.82rem">
                            {{ number_format($d->fee,0,',',' ') }} XOF
                        </td>
                        <td>
                            <span class="badge px-2 py-1 rounded-pill"
                                  style="background:{{ $d->statusColor() }};
                                         color:{{ $d->statusTextColor() }};font-size:.75rem">
                                {{ $d->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.deliveries.show', $d) }}"
                               class="btn btn-sm btn-outline-clay">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Aucune livraison</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @forelse($deliveries as $d)
        <div class="mcard">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="fw-700" style="font-size:.9rem">{{ Str::limit($d->order->title,30) }}</div>
                <span class="text-muted" style="font-size:.75rem">#{{ $d->id }}</span>
            </div>
            <div class="text-muted mb-2" style="font-size:.78rem">
                {{ $d->order->client->name }} ← {{ $d->order->artisan->name }}
            </div>
            <div class="mcard-row">
                <span class="label">Trajet</span>
                <span class="text-end">
                    <i class="bi bi-geo-alt me-1 text-clay"></i>{{ $d->pickup_city }} →
                    <i class="bi bi-geo-alt-fill me-1" style="color:#155724"></i>{{ $d->delivery_city }}
                </span>
            </div>
            <div class="mcard-row">
                <span class="label">Livreur</span>
                @if($d->livreur)
                    <span>{{ $d->livreur->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
            <div class="mcard-row">
                <span class="label">Frais</span>
                <span class="fw-700 text-clay">{{ number_format($d->fee,0,',',' ') }} XOF</span>
            </div>
            <div class="mcard-row">
                <span class="label">Statut</span>
                <span class="badge px-2 py-1 rounded-pill"
                      style="background:{{ $d->statusColor() }};color:{{ $d->statusTextColor() }}">
                    {{ $d->statusLabel() }}
                </span>
            </div>
            <div class="mcard-actions">
                <a href="{{ route('admin.deliveries.show', $d) }}" class="btn btn-sm btn-outline-clay">
                    <i class="bi bi-eye me-1"></i>Voir le détail
                </a>
            </div>
        </div>
        @empty
        <p class="text-center text-muted py-4">Aucune livraison</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $deliveries->links() }}</div>
</div>
@endsection
