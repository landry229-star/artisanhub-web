@extends('layouts.dashboard')
@section('title', 'Mission #'.$delivery->id)
@section('page-title', 'Détail mission #'.$delivery->id)

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('livreur.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('livreur.missions.index') }}" class="sidebar-link active">
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
    <a href="{{ route('livreur.missions.index') }}" class="btn btn-sm btn-outline-clay">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <h4 class="fw-700 mb-0">{{ $delivery->order->title }}</h4>
                <span class="badge px-3 py-2 rounded-pill"
                      style="background:{{ $delivery->statusColor() }};
                             color:{{ $delivery->statusTextColor() }}">
                    {{ $delivery->statusLabel() }}
                </span>
            </div>

            {{-- Timeline livraison --}}
            @php
                $steps = [
                    ['s' => 'assignee',  'label' => 'Assignée',       'icon' => 'bi-bell'],
                    ['s' => 'acceptee',  'label' => 'Acceptée',        'icon' => 'bi-check'],
                    ['s' => 'en_route',  'label' => 'En route',        'icon' => 'bi-bicycle'],
                    ['s' => 'recuperee', 'label' => 'Objet récupéré',  'icon' => 'bi-box-seam'],
                    ['s' => 'livree',    'label' => 'Livré ✅',        'icon' => 'bi-house-check'],
                ];
                $order  = ['assignee'=>0,'acceptee'=>1,'en_route'=>2,'recuperee'=>3,'livree'=>4];
                $cur    = $order[$delivery->status] ?? -1;
            @endphp

            @if(!in_array($delivery->status, ['echouee']))
                <div class="d-flex align-items-center mb-4" style="overflow-x:auto;padding:4px 0">
                    @foreach($steps as $i => $step)
                        <div class="text-center flex-shrink-0" style="min-width:72px;flex:1">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1"
                                 style="width:36px;height:36px;
                                        background:{{ $i <= $cur ? '#C4622D' : '#ECD8C6' }};
                                        color:{{ $i <= $cur ? '#fff' : '#9A8070' }}">
                                <i class="bi {{ $step['icon'] }}" style="font-size:.85rem"></i>
                            </div>
                            <div style="font-size:.68rem;
                                        color:{{ $i <= $cur ? '#C4622D' : '#9A8070' }};
                                        font-weight:{{ $i === $cur ? '700' : '400' }}">
                                {{ $step['label'] }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div style="flex:1;min-width:12px;height:2px;margin-bottom:20px;
                                        background:{{ $i < $cur ? '#C4622D' : '#ECD8C6' }}"></div>
                        @endif
                    @endforeach
                </div>
            @endif

            <hr style="border-color:#ECD8C6">

            {{-- Détails trajet --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background:#F5EFE6">
                        <div style="font-size:.72rem;color:#9A8070;text-transform:uppercase;letter-spacing:.1em">
                            📍 Récupérer chez
                        </div>
                        <div class="fw-700 mt-1">{{ $delivery->order->artisan->name }}</div>
                        <div class="text-muted" style="font-size:.85rem">
                            {{ $delivery->pickup_address ?? $delivery->pickup_city }}
                        </div>
                        @if($delivery->picked_up_at)
                            <div class="mt-1" style="font-size:.78rem;color:#155724">
                                ✅ Récupéré le {{ $delivery->picked_up_at->format('d/m H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background:#D4EDDA">
                        <div style="font-size:.72rem;color:#155724;text-transform:uppercase;letter-spacing:.1em">
                            🏠 Livrer à
                        </div>
                        <div class="fw-700 mt-1">{{ $delivery->order->client->name }}</div>
                        <div class="text-muted" style="font-size:.85rem">
                            {{ $delivery->delivery_address ?? $delivery->delivery_city }}
                        </div>
                        @if($delivery->delivered_at)
                            <div class="mt-1" style="font-size:.78rem;color:#155724">
                                ✅ Livré le {{ $delivery->delivered_at->format('d/m H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-wrap">
                @if($delivery->canBeAccepted())
                    <form action="{{ route('livreur.missions.accept', $delivery) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-clay">
                            <i class="bi bi-check-lg me-2"></i>Accepter la mission
                        </button>
                    </form>
                    <form action="{{ route('livreur.missions.refuse', $delivery) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-outline-danger"
                                onclick="return confirm('Refuser cette mission ?')">
                            <i class="bi bi-x-lg me-2"></i>Refuser
                        </button>
                    </form>
                @endif

                @if($delivery->status === 'acceptee')
                    <form action="{{ route('livreur.missions.enroute', $delivery) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-primary">
                            <i class="bi bi-bicycle me-2"></i>Je suis en route vers l'artisan
                        </button>
                    </form>
                @endif

                @if($delivery->status === 'en_route')
                    <form action="{{ route('livreur.missions.pickup', $delivery) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-warning">
                            <i class="bi bi-box-seam me-2"></i>J'ai récupéré l'objet
                        </button>
                    </form>
                @endif

                @if($delivery->status === 'recuperee')
                    <form action="{{ route('livreur.missions.deliver', $delivery) }}" method="POST">
                        @csrf @method('PATCH')
                        <label class="form-label mb-1" for="proof_code">Code remis par le client</label>
                        <input id="proof_code" name="proof_code" class="form-control mb-2"
                               inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                               placeholder="123456" required>
                        <button class="btn btn-success"
                                onclick="return confirm('Confirmer la livraison chez le client ?')">
                            <i class="bi bi-house-check me-2"></i>J'ai livré chez le client
                        </button>
                    </form>
                @endif

                @if(in_array($delivery->status, ['acceptee','en_route','recuperee']))
                    <button class="btn btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#failModal">
                        <i class="bi bi-exclamation-triangle me-2"></i>Signaler un problème
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="content-card mb-3" style="background:#F5EFE6">
            <p class="section-label mb-2">MES GAINS</p>
            <div style="font-size:1.8rem;font-weight:700;color:#C4622D">
                {{ number_format($delivery->fee, 0, ',', ' ') }} XOF
            </div>
            <p class="text-muted mb-0" style="font-size:.82rem">
                Versé après validation du client
            </p>
        </div>
        <div class="content-card mb-3">
            <p class="section-label mb-2">COMMANDE</p>
            <div class="fw-600 mb-1">{{ $delivery->order->title }}</div>
            @if($delivery->order->budget)
                <div class="text-muted" style="font-size:.82rem">
                    Valeur : {{ number_format($delivery->order->budget, 0, ',', ' ') }} XOF
                </div>
            @endif
            <span class="badge-status status-{{ $delivery->order->status }} mt-1 d-inline-block">
                {{ $delivery->order->statusLabel() }}
            </span>
        </div>
        <div class="content-card mb-3">
            <a href="{{ route('messages.thread', [$delivery->order, $delivery->order->artisan]) }}" class="mb-2 btn btn-outline-clay w-100">
                <i class="bi bi-chat-dots me-2"></i>Discuter avec l'artisan
            </a>
            <a href="{{ route('messages.thread', [$delivery->order, $delivery->order->client]) }}" class="btn btn-outline-clay w-100">
                <i class="bi bi-chat-dots me-2"></i>Discuter avec le client
            </a>
        </div>
        <div class="content-card">
            <p class="section-label mb-2">CONTACTS</p>
            <div class="mb-2">
                <div class="fw-600" style="font-size:.875rem">Artisan</div>
                <div class="text-muted" style="font-size:.82rem">{{ $delivery->order->artisan->name }}</div>
                @if($delivery->order->artisan->phone)
                    <a href="tel:{{ $delivery->order->artisan->phone }}"
                       class="btn btn-sm btn-outline-clay mt-1">
                        <i class="bi bi-telephone me-1"></i>Appeler
                    </a>
                @endif
            </div>
            <hr style="border-color:#ECD8C6">
            <div>
                <div class="fw-600" style="font-size:.875rem">Client</div>
                <div class="text-muted" style="font-size:.82rem">{{ $delivery->order->client->name }}</div>
                @if($delivery->order->client->phone)
                    <a href="tel:{{ $delivery->order->client->phone }}"
                       class="btn btn-sm btn-outline-clay mt-1">
                        <i class="bi bi-telephone me-1"></i>Appeler
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@if($delivery->isTrackable())
    <div class="mt-3 alert alert-info" id="geoloc-status" style="font-size:.85rem">
        <i class="bi bi-geo-alt me-1"></i>
        <span id="geoloc-status-text">Partage de votre position en direct avec le client et l'artisan...</span>
    </div>
@endif

{{-- Modal problème --}}
<div class="modal fade" id="failModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700">Signaler un problème</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('livreur.missions.fail', $delivery) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Décrivez le problème <span class="text-danger">*</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Ex: Adresse introuvable, client absent, objet endommagé..."
                                  required maxlength="300"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Signaler</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@if($delivery->isTrackable())
@push('scripts')
<script>
(function () {
    const url = @json(route('livreur.missions.location', $delivery));
    const statusText = document.getElementById('geoloc-status-text');

    if (!navigator.geolocation) return;

    function send(position) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            }),
        }).catch(() => {});
    }

    function onError() {
        if (statusText) statusText.textContent =
            "Position indisponible — activez la localisation pour être suivi par le client et l'artisan.";
    }

    navigator.geolocation.getCurrentPosition(send, onError, { enableHighAccuracy: true });
    navigator.geolocation.watchPosition(send, onError, { enableHighAccuracy: true, maximumAge: 10000 });
})();
</script>
@endpush
@endif
