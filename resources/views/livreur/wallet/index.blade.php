@extends('layouts.dashboard')
@section('title', 'Mon solde')
@section('page-title', 'Mon solde livreur')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('livreur.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('livreur.missions.index') }}" class="sidebar-link">
        <i class="bi bi-bicycle"></i> Mes missions
    </a>
    <a href="{{ route('livreur.wallet.index') }}" class="sidebar-link active">
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

@section('content')
<div class="mb-4 content-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <p class="mb-1 section-label">PAIEMENTS</p>
            <h4 class="mb-1 fw-700">Mon solde</h4>
            <p class="mb-0 text-muted">Total gagné : <strong>{{ number_format($stats['total_earned'], 0, ',', ' ') }} XOF</strong></p>
        </div>
        <a href="{{ route('livreur.wallet.statement') }}" class="btn btn-clay">
            <i class="bi bi-file-earmark-pdf me-1"></i>Relevé PDF
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Gains totaux', 'value' => number_format($stats['total_earned'], 0, ',', ' ') . ' XOF', 'icon' => 'bi-cash-coin', 'bg' => '#F5EFE6', 'ic' => '#C4622D'],
        ['label' => 'Ce mois', 'value' => number_format($stats['month_earned'], 0, ',', ' ') . ' XOF', 'icon' => 'bi-calendar3', 'bg' => '#D4EDDA', 'ic' => '#155724'],
        ['label' => 'Livraisons terminées', 'value' => $stats['deliveries_count'], 'icon' => 'bi-check-circle', 'bg' => '#CCE5FF', 'ic' => '#004085'],
        ['label' => 'En attente', 'value' => number_format($stats['active_value'], 0, ',', ' ') . ' XOF', 'icon' => 'bi-hourglass-split', 'bg' => '#FFF3CD', 'ic' => '#856404'],
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

<div class="content-card">
    <h5 class="fw-700 mb-3">Historique des missions</h5>
    @forelse($deliveries as $delivery)
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom" style="border-color:#F5EFE6!important">
            <div>
                <div class="fw-600">{{ $delivery->order->title }}</div>
                <div class="small text-muted">
                    {{ $delivery->order->client->name }} ·
                    {{ $delivery->delivered_at?->format('d/m/Y à H:i') ?? $delivery->created_at->format('d/m/Y à H:i') }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-700 text-clay">{{ number_format($delivery->fee, 0, ',', ' ') }} XOF</div>
                <div class="small text-muted">{{ $delivery->statusLabel() }}</div>
            </div>
        </div>
    @empty
        <p class="mb-0 py-4 text-center text-muted">Aucune livraison enregistrée pour le moment.</p>
    @endforelse
</div>
@endsection
