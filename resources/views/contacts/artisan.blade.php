@extends('layouts.dashboard')
@section('title', 'Mes contacts')
@section('page-title', 'Mes contacts')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('artisan.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('artisan.orders.index') }}" class="sidebar-link">
        <i class="bi bi-bag-check"></i> Mes commandes
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link active">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <div class="mt-2 sidebar-section-title">Mon profil</div>
    <a href="{{ route('artisan.portfolio.index') }}" class="sidebar-link">
        <i class="bi bi-images"></i> Mon portfolio
    </a>
    <a href="{{ route('artisan.profile.edit') }}" class="sidebar-link">
        <i class="bi bi-person-gear"></i> Modifier mon profil
    </a>
@endsection

@section('content')
<div class="content-card">
    <h5 class="mb-3 fw-700"><i class="bi bi-people me-2 text-clay"></i>Clients déjà servis</h5>
    @forelse($clients as $c)
        <div class="gap-3 py-2 d-flex align-items-center" style="border-bottom:1px solid #ECD8C6">
            <img src="{{ $c->avatarUrl() }}" class="rounded-circle" width="44" height="44" style="object-fit:cover">
            <div class="flex-grow-1">
                <div class="fw-600">{{ $c->name }}</div>
                <div class="text-muted" style="font-size:.78rem">📍 {{ $c->city }}
                    @if($c->phone) · 📞 {{ $c->phone }} @endif
                </div>
            </div>
        </div>
    @empty
        <p class="mb-0 text-muted" style="font-size:.85rem">Aucun client servi pour le moment.</p>
    @endforelse
</div>
@endsection
