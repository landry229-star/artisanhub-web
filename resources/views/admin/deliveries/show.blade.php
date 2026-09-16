@extends('layouts.dashboard')
@section('title', 'Livraison #' . $delivery->id)
@section('page-title', 'Détail livraison #' . $delivery->id)

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('content')
<div class="row g-4">
<div class="col-lg-8">

    {{-- Statut + trajet --}}
    <div class="content-card mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
            <div>
                <p class="section-label mb-1">Commande liée</p>
                <h5 class="fw-700 mb-0">{{ $delivery->order->title }}</h5>
            </div>
            <span class="badge px-3 py-2 fs-6"
                  style="background:{{ $delivery->statusColor() }};color:{{ $delivery->statusTextColor() }}">
                {{ $delivery->statusLabel() }}
            </span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-3" style="background:#F5EFE6;border:1px solid #ECD8C6">
                    <p class="section-label mb-2"><i class="bi bi-box2 me-1"></i>Enlèvement</p>
                    <p class="fw-700 mb-1">{{ $delivery->order->artisan->name }}</p>
                    <p class="text-muted mb-0" style="font-size:.85rem">
                        📍 {{ $delivery->pickup_city }}<br>
                        {{ $delivery->pickup_address ?? '—' }}
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-3" style="background:#f0f7f0;border:1px solid #c3e6cb">
                    <p class="section-label mb-2"><i class="bi bi-house me-1"></i>Livraison</p>
                    <p class="fw-700 mb-1">{{ $delivery->order->client->name }}</p>
                    <p class="text-muted mb-0" style="font-size:.85rem">
                        📍 {{ $delivery->delivery_city }}<br>
                        {{ $delivery->delivery_address ?? '—' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-3 text-center">
            <div class="col-4">
                <p class="section-label mb-1">Frais</p>
                <p class="fw-700 mb-0" style="color:#C4622D;font-size:1.1rem">
                    {{ number_format($delivery->fee,0,',',' ') }} XOF
                </p>
            </div>
            <div class="col-4">
                <p class="section-label mb-1">Colis récupéré</p>
                <p class="fw-600 mb-0" style="font-size:.8rem">
                    {{ $delivery->picked_up_at?->format('d/m/Y H:i') ?? '—' }}
                </p>
            </div>
            <div class="col-4">
                <p class="section-label mb-1">Livré le</p>
                <p class="fw-600 mb-0" style="font-size:.8rem">
                    {{ $delivery->delivered_at?->format('d/m/Y H:i') ?? '—' }}
                </p>
            </div>
        </div>

        @if($delivery->notes)
        <div class="mt-3 p-3 rounded-3" style="background:#fff3cd;border:1px solid #ffc107">
            <p class="section-label mb-1">⚠️ Notes / Problème signalé</p>
            <p class="mb-0" style="font-size:.88rem">{{ $delivery->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Livreur --}}
    <div class="content-card mb-4">
        <h5 class="fw-700 mb-3"><i class="bi bi-bicycle me-2" style="color:#C4622D"></i>Livreur</h5>

        @if($delivery->livreur)
        <div class="d-flex gap-3 align-items-center mb-3 flex-wrap">
            <img src="{{ $delivery->livreur->avatarUrl() }}" class="rounded-circle"
                 width="56" height="56" style="object-fit:cover;border:2px solid #C4622D">
            <div>
                <p class="fw-700 mb-0">{{ $delivery->livreur->name }}</p>
                <p class="text-muted mb-0" style="font-size:.85rem">
                    📍 {{ $delivery->livreur->city }} —
                    {{ $delivery->livreur->phone ?? 'Tél non renseigné' }}
                </p>
                <span class="badge" style="background:{{ $delivery->livreur->is_livreur_available ? '#d4edda' : '#f8d7da' }};color:{{ $delivery->livreur->is_livreur_available ? '#155724' : '#721c24' }};font-size:.75rem">
                    {{ $delivery->livreur->is_livreur_available ? '● Disponible' : '● Occupé' }}
                </span>
            </div>
        </div>
        @else
        <p class="text-muted mb-3"><i class="bi bi-search me-2"></i>Aucun livreur assigné pour l'instant.</p>
        @endif

        {{-- Réassignation manuelle --}}
        @if(!in_array($delivery->status, ['livree','echouee']))
        <div class="border-top pt-3 mt-2" style="border-color:#ECD8C6!important">
            <p class="fw-600 mb-2" style="font-size:.88rem">Réassigner manuellement :</p>
            {{-- Anciennement "max-width:280px" fixe sur le select : débordait / restait minuscule sur mobile.
                 Passe en colonne pleine largeur sous 576px via .reassign-form --}}
            <form action="{{ route('admin.deliveries.reassign', $delivery) }}" method="POST" class="d-flex gap-2 reassign-form">
                @csrf @method('PATCH')
                <select name="livreur_id" class="form-select" required style="max-width:280px">
                    <option value="">— Choisir un livreur —</option>
                    @foreach(\App\Models\User::where('role','livreur')->where('is_active',true)->orderBy('name')->get() as $l)
                        <option value="{{ $l->id }}" {{ $delivery->livreur_id === $l->id ? 'selected' : '' }}>
                            {{ $l->name }} ({{ $l->city }})
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-clay px-3">
                    <i class="bi bi-arrow-repeat me-1"></i>Réassigner
                </button>
            </form>
        </div>
        @endif
    </div>

</div>

{{-- Sidebar infos commande --}}
<div class="col-lg-4">
    <div class="content-card position-sticky d-none d-lg-block" style="top:80px">
        @include('admin.deliveries._order-info', ['delivery' => $delivery])
    </div>
    {{-- Sur mobile, la sidebar sticky se transforme en bloc normal en bas de page --}}
    <div class="content-card d-lg-none">
        @include('admin.deliveries._order-info', ['delivery' => $delivery])
    </div>
</div>
</div>
@endsection
