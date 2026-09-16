@extends('layouts.dashboard')
@section('title', 'Mes contacts')
@section('page-title', 'Mes contacts')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('livreur.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('livreur.missions.index') }}" class="sidebar-link">
        <i class="bi bi-bicycle"></i> Mes missions
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link active">
        <i class="bi bi-people"></i> Mes contacts
    </a>
@endsection

@section('content')
<div class="content-card">
    <h5 class="mb-3 fw-700"><i class="bi bi-people me-2 text-clay"></i>Clients déjà livrés</h5>
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
        <p class="mb-0 text-muted" style="font-size:.85rem">Aucun client livré pour le moment.</p>
    @endforelse
</div>
@endsection
