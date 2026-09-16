@extends('layouts.dashboard')
@section('title', 'Mes artisans favoris')
@section('page-title', 'Mes favoris')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('client.dashboard') }}" class="sidebar-link"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
    <a href="{{ route('client.orders.index') }}" class="sidebar-link"><i class="bi bi-bag"></i> Mes commandes</a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <a href="{{ route('client.favorites.index') }}" class="sidebar-link active"><i class="bi bi-heart"></i> Mes favoris</a>
    <div class="sidebar-section-title mt-2">Découvrir</div>
    <a href="{{ route('artisans.index') }}" class="sidebar-link"><i class="bi bi-search"></i> Explorer</a>
@endsection

@section('content')
@if($artisans->isEmpty())
    <div class="content-card text-center py-5">
        <i class="bi bi-heart" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem"></i>
        <h5 class="fw-700">Aucun favori pour le moment</h5>
        <p class="text-muted">Ajoutez des artisans à vos favoris pour les retrouver facilement.</p>
        <a href="{{ route('artisans.index') }}" class="btn btn-clay mt-2">
            <i class="bi bi-search me-2"></i>Explorer les artisans
        </a>
    </div>
@else
    <div class="row g-4">
        @foreach($artisans as $artisan)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-3">
                    <div class="d-flex gap-3 align-items-start mb-3">
                        <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle flex-shrink-0"
                             width="56" height="56"
                             style="object-fit:cover;border:2px solid #C4622D">
                        <div class="flex-grow-1">
                            <div class="fw-700">{{ $artisan->name }}</div>
                            <div class="text-muted" style="font-size:.82rem">
                                {{ $artisan->artisanProfile?->specialty }}
                            </div>
                            <div class="text-muted" style="font-size:.78rem">
                                📍 {{ $artisan->city }}
                            </div>
                        </div>
                        {{-- Bouton retirer du favori --}}
                        <form action="{{ route('favorites.toggle', $artisan->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm p-1" style="color:#C4622D;background:none;border:none"
                                    title="Retirer des favoris"
                                    data-confirm="Retirer cet artisan de vos favoris ?">
                                <i class="bi bi-heart-fill" style="font-size:1.1rem"></i>
                            </button>
                        </form>
                    </div>

                    @if($artisan->artisanProfile?->rating > 0)
                        <div class="mb-2">
                            <span style="color:#D4A853;font-size:.85rem">
                                @for($i=1;$i<=5;$i++)
                                    <i class="bi bi-star{{ $i<=round($artisan->artisanProfile->rating)?'-fill':'' }}"></i>
                                @endfor
                            </span>
                            <span class="text-muted ms-1" style="font-size:.8rem">
                                {{ $artisan->artisanProfile->rating }} ({{ $artisan->artisanProfile->reviews_count }})
                            </span>
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('artisans.show', $artisan->routeSlug()) }}"
                           class="btn btn-sm btn-outline-clay flex-grow-1">Voir le profil</a>
                        <a href="{{ route('client.orders.create', $artisan->id) }}"
                           class="btn btn-sm btn-clay">Commander</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
