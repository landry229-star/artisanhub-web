@extends('layouts.dashboard')
@section('title', 'Mes commandes')
@section('page-title', 'Mes commandes')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')
<div class="content-card">

    {{-- Filtres --}}
    <form method="GET" class="mb-4 row g-2">
        <div class="col-md-4">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                @foreach([
                    'en_attente' => 'En attente',
                    'acceptee'   => 'Acceptée',
                    'en_cours'   => 'En cours',
                    'livree'     => 'Livrée',
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
        <div class="p-3 mb-3 border rounded-3" style="border-color:#ECD8C6!important">

            {{-- En-tête commande --}}
            <div class="flex-wrap gap-2 d-flex justify-content-between align-items-start">
                <div class="gap-3 d-flex align-items-start">
                    <img src="{{ $order->client->avatarUrl() }}" class="mt-1 rounded-circle"
                         width="44" height="44" style="object-fit:cover;flex-shrink:0">
                    <div>
                        <div class="fw-700">{{ $order->title }}</div>
                        <div class="text-muted" style="font-size:.82rem">
                            Client : {{ $order->client->name }} · {{ $order->client->city }}
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
                <div class="flex-wrap gap-2 d-flex align-items-center">
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

            {{-- Description courte --}}
            <p class="mt-2 mb-3 text-muted" style="font-size:.875rem">
                {{ Str::limit($order->description, 120) }}
            </p>

            {{-- ══ BOUTONS D'ACTION ══ --}}
            <div class="flex-wrap gap-2 d-flex align-items-center">

                {{-- Toujours visible : Voir le détail --}}
                <a href="{{ route('artisan.orders.show', $order) }}"
                   class="btn btn-sm btn-outline-clay">
                    <i class="bi bi-eye me-1"></i>Voir
                </a>

                {{-- ① ACCEPTER / REFUSER (statut : en_attente) --}}
                @if($order->canBeAccepted())
                    @php $paymentOk = $order->payment && $order->payment->status === 'completed'; @endphp
                    @if(!$paymentOk)
                        <span class="px-2 py-1 badge" style="background:#FFF3CD;color:#856404;font-size:.75rem">
                            <i class="bi bi-hourglass-split me-1"></i>Paiement non confirmé
                        </span>
                    @endif
                    <form action="{{ route('artisan.orders.accept', $order) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-clay"
                                {{ !$paymentOk ? 'disabled' : '' }}
                                onclick="return confirm('Accepter cette commande ?')">
                            <i class="bi bi-check-lg me-1"></i>Accepter
                        </button>
                    </form>
                    <form action="{{ route('artisan.orders.reject', $order) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="reason" value="La commande ne correspond pas à mes disponibilités ou compétences.">
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Refuser cette commande avec le motif par défaut ?')">
                            <i class="bi bi-x-lg me-1"></i>Refuser
                        </button>
                    </form>
                @endif

                {{-- ② DÉMARRER LE TRAVAIL (statut : acceptee) --}}
                @if($order->canBeStarted())
                    <form action="{{ route('artisan.orders.start', $order) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-primary"
                                onclick="return confirm('Démarrer le travail sur cette commande ?')">
                            <i class="bi bi-tools me-1"></i>Démarrer le travail
                        </button>
                    </form>
                @endif

                {{-- ③ MARQUER COMME LIVRÉE (statut : en_cours) — la photo de fin de travaux
                     est obligatoire, on redirige donc vers la fiche commande qui contient
                     la modal d'upload plutôt que de soumettre un formulaire sans fichier. --}}
                @if($order->canBeDelivered())
                    <a href="{{ route('artisan.orders.show', $order) }}#deliverModal"
                       class="btn btn-sm btn-success" onclick="sessionStorage.setItem('open_deliver_modal','1')">
                        <i class="bi bi-truck me-1"></i>Livrer (photo requise)
                    </a>
                @endif

                {{-- ④ EN ATTENTE DE PAIEMENT (statut : livree) --}}
                @if($order->status === \App\Models\Order::STATUS_DELIVERED)
                    <span class="px-2 py-1 badge"
                          style="background:#FFF3CD;color:#856404;font-size:.78rem">
                        <i class="bi bi-hourglass-split me-1"></i>
                        En attente du paiement client
                    </span>
                @endif

                {{-- Messagerie (acceptee, en_cours, livree) --}}
                @if(in_array($order->status, ['acceptee', 'en_cours', 'livree']))
                    <a href="{{ route('messages.index', $order) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chat me-1"></i>Messages
                        @php
                            $unread = $order->messages
                                ->where('sender_id', '!=', auth()->id())
                                ->whereNull('read_at')
                                ->count();
                        @endphp
                        @if($unread > 0)
                            <span class="badge ms-1"
                                  style="background:var(--clay)">{{ $unread }}</span>
                        @endif
                    </a>
                @endif

            </div>
            {{-- ══ FIN BOUTONS ══ --}}

        </div>
    @empty
        <div class="py-5 text-center text-muted">
            <i class="mb-3 bi bi-inbox display-3 d-block" style="opacity:.25"></i>
            <p>Aucune commande trouvée</p>
        </div>
    @endforelse

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
