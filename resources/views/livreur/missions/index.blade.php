@extends('layouts.dashboard')
@section('title', 'Mes missions')
@section('page-title', 'Mes missions de livraison')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('livreur.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('livreur.missions.index') }}" class="sidebar-link active">
        <i class="bi bi-truck"></i> Mes missions
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
    <div class="sidebar-section-title mt-2">Disponibilité</div>
    <form action="{{ route('livreur.availability') }}" method="POST">
        @csrf @method('PATCH')
        <button type="submit" class="sidebar-link w-100 text-start border-0 bg-transparent">
            <i class="bi bi-toggle-on"></i> Changer ma dispo
        </button>
    </form>
@endsection

@section('content')
<div class="row g-4">

    {{-- Missions actives --}}
    <div class="col-12">
        <div class="content-card">
            <h5 class="fw-700 mb-4"><i class="bi bi-truck me-2" style="color:#C4622D"></i>Missions en cours ({{ $missions->count() }})</h5>

            @if($missions->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-3 d-block mb-3" style="opacity:.2"></i>
                    <p class="fw-600">Aucune mission en cours</p>
                    <p style="font-size:.85rem">Vous recevrez une notification dès qu'une mission vous sera assignée.</p>
                </div>
            @else
                <div class="row g-3">
                @foreach($missions as $delivery)
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100" style="border-color:#e0d5c5!important">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge px-2 py-1" style="background:{{ $delivery->statusColor() }};color:{{ $delivery->statusTextColor() }};font-size:.75rem">
                                {{ $delivery->statusLabel() }}
                            </span>
                            <span class="text-muted" style="font-size:.75rem">{{ $delivery->created_at->format('d/m/Y') }}</span>
                        </div>
                        <h6 class="fw-700 mb-1" style="font-size:.9rem">{{ Str::limit($delivery->order->title, 40) }}</h6>
                        <p class="text-muted mb-2" style="font-size:.8rem">
                            <i class="bi bi-person me-1"></i>{{ $delivery->order->client->name }}<br>
                            <i class="bi bi-geo-alt me-1"></i>{{ $delivery->pickup_city }} → {{ $delivery->delivery_city }}<br>
                            <i class="bi bi-cash me-1"></i>Frais : <strong>{{ number_format($delivery->fee,0,',',' ') }} XOF</strong>
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($delivery->canBeAccepted())
                                <form action="{{ route('livreur.missions.accept', $delivery) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success py-1 px-2" style="font-size:.8rem">
                                        <i class="bi bi-check-lg me-1"></i>Accepter
                                    </button>
                                </form>
                                <form action="{{ route('livreur.missions.refuse', $delivery) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm py-1 px-2" style="font-size:.8rem;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24"
                                            onclick="return confirm('Refuser cette mission ?')">
                                        <i class="bi bi-x-lg me-1"></i>Refuser
                                    </button>
                                </form>
                            @elseif($delivery->canBeStarted())
                                <form action="{{ route('livreur.missions.enroute', $delivery) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-clay py-1 px-2" style="font-size:.8rem">
                                        <i class="bi bi-bicycle me-1"></i>En route vers artisan
                                    </button>
                                </form>
                            @elseif($delivery->canBePickedUpFromArtisan())
                                <form action="{{ route('livreur.missions.pickup', $delivery) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-clay py-1 px-2" style="font-size:.8rem">
                                        <i class="bi bi-box-seam me-1"></i>Colis récupéré
                                    </button>
                                </form>
                            @elseif($delivery->canBeDelivered())
                                <form action="{{ route('livreur.missions.deliver', $delivery) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm py-1 px-2" style="font-size:.8rem;background:#d4edda;border:1px solid #c3e6cb;color:#155724"
                                            onclick="return confirm('Confirmer la remise du colis au client ?')">
                                        <i class="bi bi-check2-all me-1"></i>Livré au client
                                    </button>
                                </form>
                                <button class="btn btn-sm py-1 px-2" style="font-size:.8rem;background:#fff3cd;border:1px solid #ffc107;color:#856404"
                                        data-bs-toggle="modal" data-bs-target="#failModal{{ $delivery->id }}">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Problème
                                </button>
                                {{-- Modal problème --}}
                                <div class="modal fade" id="failModal{{ $delivery->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0">
                                                <h6 class="modal-title fw-700">Signaler un problème</h6>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('livreur.missions.fail', $delivery) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <div class="modal-body pt-0">
                                                    <textarea name="notes" class="form-control" rows="3"
                                                              placeholder="Décrivez le problème rencontré..." required maxlength="300"></textarea>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="submit" class="btn btn-warning">Signaler</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <a href="{{ route('livreur.missions.show', $delivery) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:.8rem">
                                <i class="bi bi-eye me-1"></i>Détails
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Historique --}}
    @if($history->isNotEmpty())
    <div class="col-12">
        <div class="content-card">
            <h5 class="fw-700 mb-4"><i class="bi bi-clock-history me-2" style="color:#9A8070"></i>Historique</h5>
            <div class="table-responsive">
                <table class="table" style="font-size:.85rem">
                    <thead>
                        <tr style="border-bottom:2px solid #ECD8C6">
                            <th>Commande</th><th>Client</th><th>Trajet</th><th>Frais</th><th>Statut</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($history as $h)
                    <tr style="border-bottom:1px solid #f0e8de;vertical-align:middle">
                        <td class="fw-600">{{ Str::limit($h->order->title, 25) }}</td>
                        <td>{{ $h->order->client->name }}</td>
                        <td style="font-size:.78rem">{{ $h->pickup_city }} → {{ $h->delivery_city }}</td>
                        <td>{{ number_format($h->fee,0,',',' ') }} XOF</td>
                        <td>
                            <span class="badge px-2 py-1" style="background:{{ $h->statusColor() }};color:{{ $h->statusTextColor() }};font-size:.72rem">
                                {{ $h->statusLabel() }}
                            </span>
                        </td>
                        <td style="font-size:.78rem">{{ $h->delivered_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
