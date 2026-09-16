@extends('layouts.dashboard')
@section('title', 'Mes commandes')
@section('page-title', 'Mes commandes')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('client.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('client.orders.index') }}" class="sidebar-link active">
        <i class="bi bi-bag"></i> Mes commandes
    </a>
    <a href="{{ route('client.wallet.index') }}" class="sidebar-link">
        <i class="bi bi-receipt"></i> Mes paiements
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <div class="sidebar-section-title mt-2">Découvrir</div>
    <a href="{{ route('artisans.index') }}" class="sidebar-link">
        <i class="bi bi-search"></i> Explorer les artisans
    </a>
@endsection

@section('topbar-actions')
    <a href="{{ route('artisans.index') }}" class="btn btn-clay btn-sm">
        <i class="bi bi-plus me-1"></i>Nouvelle commande
    </a>
@endsection

@section('content')
<div class="content-card">

    {{-- Filtres --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                @foreach([
                    'en_attente' => 'En attente',
                    'acceptee'   => 'Acceptée',
                    'en_cours'   => 'En cours',
                    'livree'     => 'Livrée — à valider',
                    'terminee'   => 'Terminée',
                    'annulee'    => 'Annulée',
                    'litige'     => 'Litige',
                ] as $val => $lab)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                        {{ $lab }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    @forelse($orders as $order)
        <div class="border rounded-3 p-3 mb-3" style="border-color:#ECD8C6!important">

            {{-- En-tête --}}
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div class="d-flex gap-3 align-items-start">
                    <img src="{{ $order->artisan->avatarUrl() }}" class="rounded-circle mt-1"
                         width="44" height="44" style="object-fit:cover;flex-shrink:0">
                    <div>
                        <div class="fw-700">{{ $order->title }}</div>
                        <div class="text-muted" style="font-size:.82rem">
                            Artisan : <strong>{{ $order->artisan->name }}</strong>
                            · {{ $order->artisan->artisanProfile?->specialty }}
                        </div>
                        <div class="text-muted" style="font-size:.82rem">
                            <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->format('d/m/Y') }}
                            @if($order->deadline)
                                &nbsp;·&nbsp;
                                <i class="bi bi-flag me-1"></i>Délai : {{ $order->deadline->format('d/m/Y') }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-status status-{{ $order->status }}">
                        {{ $order->statusLabel() }}
                    </span>
                    @if($order->budget)
                        <span class="fw-700 text-clay">
                            {{ number_format($order->budget, 0, ',', ' ') }} XOF
                        </span>
                    @endif
                </div>
            </div>

            {{-- Message contextuel selon le statut --}}
            <div class="mt-2 mb-3">
                @if($order->status === 'en_attente')
                    <p class="mb-0 text-muted" style="font-size:.82rem">
                        <i class="bi bi-hourglass me-1"></i>
                        En attente de réponse de l'artisan — sous 48h
                    </p>
                @elseif($order->status === 'acceptee')
                    <p class="mb-0" style="font-size:.82rem;color:#055160">
                        <i class="bi bi-check-circle me-1"></i>
                        L'artisan a accepté votre commande. Il va démarrer le travail prochainement.
                    </p>
                @elseif($order->status === 'en_cours')
                    <p class="mb-0" style="font-size:.82rem;color:#004085">
                        <i class="bi bi-tools me-1"></i>
                        L'artisan travaille sur votre commande. Vous pouvez lui envoyer un message.
                    </p>
                @elseif($order->status === 'livree')
                    <p class="mb-0 fw-600" style="font-size:.82rem;color:#155724">
                        <i class="bi bi-truck me-1"></i>
                        ✅ L'artisan a livré ! Validez pour déclencher le paiement.
                    </p>
                @elseif($order->status === 'terminee')
                    <p class="mb-0" style="font-size:.82rem;color:#155724">
                        <i class="bi bi-star me-1"></i>
                        Commande terminée et payée.
                        @if(!$order->review) Pensez à laisser un avis ! @endif
                    </p>
                @elseif($order->status === 'litige')
                    <p class="mb-0" style="font-size:.82rem;color:#721C24">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Litige en cours — notre équipe intervient sous 48h.
                    </p>
                @elseif($order->status === 'annulee')
                    <p class="mb-0 text-muted" style="font-size:.82rem">
                        <i class="bi bi-x-circle me-1"></i>
                        Commande annulée.
                    </p>
                @endif
            </div>

            {{-- ══ BOUTONS D'ACTION CLIENT ══ --}}
            <div class="d-flex gap-2 flex-wrap align-items-center">

                {{-- Voir le détail --}}
                <a href="{{ route('client.orders.show', $order) }}"
                   class="btn btn-sm btn-outline-clay">
                    <i class="bi bi-eye me-1"></i>Détails
                </a>

                {{-- ① ANNUlER AVANT DÉMARRAGE --}}
                @if($order->canBeCancelled())
                    <form action="{{ route('client.orders.cancel', $order) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <textarea name="reason" class="mb-2 form-control form-control-sm" rows="2"
                                  minlength="5" maxlength="500"
                                  placeholder="Motif de l'annulation (5 caractères minimum)." required></textarea>
                        <button type="submit" class="btn btn-sm btn-outline-secondary"
                            onclick="return confirm('Annuler cette commande ? L\'artisan sera informé.')">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>
                    </form>
                @endif
                @if($order->artisan && $order->service_id)
                    <a href="{{ route('client.orders.create', [$order->artisan_id, 'service' => $order->service_id, 'repeat' => $order->id]) }}"
                       class="btn btn-sm btn-outline-clay">
                        <i class="bi bi-arrow-repeat me-1"></i>Recommander
                    </a>
                @endif

                {{-- ② LIVREE → Valider et payer ou signaler litige --}}
                @if($order->canBeValidated())
                    <form action="{{ route('client.orders.validate', $order) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success"
                                onclick="return confirm('Valider la livraison et procéder au paiement ?')">
                            <i class="bi bi-check-circle me-1"></i>Valider et payer
                        </button>
                    </form>
                    <form action="{{ route('client.orders.dispute', $order) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Signaler un problème sur cette livraison ?')">
                            <i class="bi bi-flag me-1"></i>Signaler un problème
                        </button>
                    </form>
                @endif

                {{-- ② TERMINEE → Laisser un avis --}}
                @if($order->status === 'terminee' && !$order->review)
                    <button class="btn btn-sm btn-outline-clay"
                            data-bs-toggle="modal"
                            data-bs-target="#reviewModal{{ $order->id }}">
                        <i class="bi bi-star me-1"></i>Laisser un avis
                    </button>
                @endif

                {{-- ③ MESSAGERIE (acceptee, en_cours, livree) --}}
                @if(in_array($order->status, ['acceptee', 'en_cours', 'livree']))
                    <a href="{{ route('messages.index', $order) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chat me-1"></i>Messages
                    </a>
                @endif

            </div>
            {{-- ══ FIN BOUTONS ══ --}}

        </div>

        {{-- Modal avis --}}
        @if($order->status === 'terminee' && !$order->review)
            <div class="modal fade" id="reviewModal{{ $order->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-700">
                                Votre avis pour {{ $order->artisan->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('reviews.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <div class="modal-body">

                                {{-- Étoiles --}}
                                <div class="mb-3 text-center">
                                    <label class="form-label fw-600 d-block">
                                        Note <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex justify-content-center gap-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating" value="{{ $i }}"
                                                   id="star{{ $order->id }}-{{ $i }}"
                                                   class="d-none star-input" required>
                                            <label for="star{{ $order->id }}-{{ $i }}"
                                                   class="star-label"
                                                   style="font-size:2rem;cursor:pointer;color:#ccc;transition:.1s">
                                                ★
                                            </label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-600">Commentaire</label>
                                    <textarea name="comment" class="form-control" rows="3"
                                              placeholder="Partagez votre expérience avec cet artisan..."></textarea>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-clay">
                                    <i class="bi bi-send me-1"></i>Publier l'avis
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bag" style="font-size:3rem;opacity:.25;display:block;margin-bottom:1rem"></i>
            <p class="mb-2">Aucune commande pour le moment</p>
            <a href="{{ route('artisans.index') }}" class="btn btn-clay btn-sm">
                <i class="bi bi-search me-1"></i>Trouver un artisan
            </a>
        </div>
    @endforelse

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
// Étoiles interactives dans les modals d'avis
document.querySelectorAll('.modal').forEach(modal => {
    const labels = modal.querySelectorAll('.star-label');
    labels.forEach((label, index) => {
        label.addEventListener('mouseover', () => {
            labels.forEach((l, i) => l.style.color = i <= index ? '#D4A853' : '#ccc');
        });
        label.addEventListener('mouseout', () => {
            const checked = modal.querySelector('.star-input:checked');
            const checkedIndex = checked ? parseInt(checked.value) - 1 : -1;
            labels.forEach((l, i) => l.style.color = i <= checkedIndex ? '#D4A853' : '#ccc');
        });
        label.addEventListener('click', () => {
            labels.forEach((l, i) => l.style.color = i <= index ? '#D4A853' : '#ccc');
        });
    });
});
</script>
@endpush
