@extends('layouts.dashboard')
@section('title', 'Conversation #' . $order->id)
@section('page-title', 'Conversation — ' . Str::limit($order->title, 40))

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('content')
<div class="row g-4">
<div class="col-lg-8">

    {{-- Auto-détection info --}}
    @if($autoFlagged > 0)
    <div class="alert py-2 mb-3" style="background:#fff3cd;border:1px solid #ffc107;font-size:.85rem">
        <i class="bi bi-robot me-2"></i>
        <strong>{{ $autoFlagged }} message(s)</strong> automatiquement signalé(s) lors de cette analyse.
    </div>
    @endif

    {{-- Alerte active --}}
    @if($order->has_alert)
    <div class="content-card mb-3" style="background:#fff3cd;border:2px solid #ffc107">
        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div style="min-width:200px">
                <p class="fw-700 mb-1" style="color:#856404">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Alerte active envoyée au client
                </p>
                <p class="mb-1" style="font-size:.85rem">{{ $order->alert_message }}</p>
                <p class="text-muted mb-0" style="font-size:.75rem">
                    Envoyée le {{ $order->alerted_at?->format('d/m/Y H:i') }}
                </p>
            </div>
            <form action="{{ route('admin.orders.clear-alert', $order) }}" method="POST">
                @csrf @method('PATCH')
                <button class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size:.78rem">
                    <i class="bi bi-x-lg me-1"></i>Retirer
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Messages --}}
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-700 mb-0">{{ $messages->count() }} message(s)</h5>
            <span class="badge" style="background:{{ $messages->where('is_flagged',true)->count()>0?'#f8d7da':'#d4edda' }};color:{{ $messages->where('is_flagged',true)->count()>0?'#721c24':'#155724' }}">
                {{ $messages->where('is_flagged',true)->count() }} signalé(s)
            </span>
        </div>

        @forelse($messages as $msg)
        <div class="mb-3 p-3 rounded-3 {{ $msg->isFlagged() ? 'border border-danger' : 'border' }}"
             style="border-color:{{ $msg->isFlagged() ? '#f5c6cb' : '#f0e8de' }}!important;
                    background:{{ $msg->isFlagged() ? '#fff8f8' : '#fafafa' }}">
            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $msg->sender->avatarUrl() }}" class="rounded-circle"
                         width="32" height="32" style="object-fit:cover;flex-shrink:0">
                    <div>
                        <span class="fw-700" style="font-size:.88rem">{{ $msg->sender->name }}</span>
                        <span class="badge ms-1 px-2" style="font-size:.7rem;background:{{ $msg->sender->role==='artisan'?'#C4622D':'#2E7D32' }};color:#fff">
                            {{ $msg->sender->role }}
                        </span>
                    </div>
                </div>
                <span class="text-muted" style="font-size:.75rem">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <p class="mb-2" style="font-size:.88rem;line-height:1.5;word-break:break-word">{{ $msg->body }}</p>

            {{-- Badge suspect --}}
            @if($msg->isFlagged())
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="badge px-2 py-1" style="background:#f8d7da;color:#721c24;font-size:.72rem">
                    <i class="bi bi-flag-fill me-1"></i>{{ $msg->flag_reason }}
                </span>
                <div class="d-flex gap-1">
                    <form action="{{ route('admin.messages.unflag', $msg) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm py-1 px-2" style="font-size:.72rem;background:#d4edda;border:1px solid #c3e6cb;color:#155724">
                            <i class="bi bi-check-lg me-1"></i>Désignaler
                        </button>
                    </form>
                    <form action="{{ route('admin.messages.destroy', $msg) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm py-1 px-2" style="font-size:.72rem;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24"
                                onclick="return confirm('Supprimer définitivement ce message ?')">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="text-end">
                <button class="btn btn-sm py-1 px-2" style="font-size:.72rem;background:#fff3cd;border:1px solid #ffc107;color:#856404"
                        data-bs-toggle="collapse" data-bs-target="#flagForm{{ $msg->id }}">
                    <i class="bi bi-flag me-1"></i>Signaler
                </button>
                {{-- Anciennement "d-flex gap-2" fixe : débordait sur mobile car l'input + le bouton
                     ne pouvaient pas se réduire. Passe en colonne sous 576px via .flag-form-inline --}}
                <div class="collapse mt-2" id="flagForm{{ $msg->id }}">
                    <form action="{{ route('admin.messages.flag', $msg) }}" method="POST" class="d-flex gap-2 flag-form-inline">
                        @csrf @method('PATCH')
                        <input type="text" name="flag_reason" class="form-control form-control-sm"
                               placeholder="Raison du signalement..." required maxlength="255" style="font-size:.82rem">
                        <button type="submit" class="btn btn-sm btn-warning py-1 px-2" style="font-size:.8rem;white-space:nowrap">
                            Confirmer
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        @empty
        <p class="text-center text-muted py-4">Aucun message dans cette conversation</p>
        @endforelse
    </div>

</div>

{{-- Sidebar infos + actions --}}
<div class="col-lg-4">
    <div class="content-card mb-3">
        <p class="section-label mb-3">Infos commande</p>
        <div style="font-size:.85rem">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Client</span>
                <strong>{{ $order->client->name }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Artisan</span>
                <strong>{{ $order->artisan->name }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Statut</span>
                <span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Budget</span>
                <strong>{{ number_format($order->budget,0,',',' ') }} XOF</strong>
            </div>
        </div>

        {{-- Envoyer une alerte au client --}}
        @if(!$order->has_alert)
        <div class="border-top pt-3" style="border-color:#ECD8C6!important">
            <p class="fw-600 mb-2" style="font-size:.85rem">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>Alerter le client
            </p>
            <form action="{{ route('admin.orders.alert', $order) }}" method="POST">
                @csrf
                <textarea name="alert_message" class="form-control mb-2" rows="3"
                          placeholder="Ex: Nous avons détecté une tentative de paiement hors plateforme. Ne payez que via ArtisanHub."
                          required maxlength="500" style="font-size:.82rem"></textarea>
                <button type="submit" class="btn btn-warning w-100 btn-sm">
                    <i class="bi bi-send me-2"></i>Envoyer l'alerte
                </button>
            </form>
        </div>
        @endif
    </div>

    <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary w-100 btn-sm">
        ← Retour aux conversations
    </a>
</div>
</div>
@endsection
