@extends('layouts.dashboard')
@section('title', 'Commande #'.$order->id)
@section('page-title', 'Commande #'.$order->id)

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('topbar-actions')
    <a href="{{ route('artisan.orders.index') }}" class="btn btn-sm btn-outline-clay">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
@endsection

@section('content')
<div class="row g-4">

    {{-- ── Colonne principale ── --}}
    <div class="col-lg-8">

        {{-- Infos commande --}}
        <div class="mb-4 content-card">
            <div class="flex-wrap gap-2 mb-3 d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1 fw-700">{{ $order->title }}</h4>
                    <span class="text-muted" style="font-size:.85rem">
                        Commande #{{ $order->id }} · {{ $order->created_at->format('d/m/Y à H:i') }}
                    </span>
                </div>
                <span class="badge-status status-{{ $order->status }} px-3 py-2">
                    {{ $order->statusLabel() }}
                </span>
            </div>

            {{-- Timeline statut --}}
            @php
                $steps = [
                    ['status' => 'en_attente',  'label' => 'Reçue',         'icon' => 'bi-inbox'],
                    ['status' => 'acceptee',    'label' => 'Acceptée',      'icon' => 'bi-check'],
                    ['status' => 'en_cours',    'label' => 'En cours',      'icon' => 'bi-tools'],
                    ['status' => 'livree',      'label' => 'Livrée',        'icon' => 'bi-truck'],
                    ['status' => 'terminee',    'label' => 'Terminée',      'icon' => 'bi-star'],
                ];
                $statusOrder = ['en_attente'=>0,'acceptee'=>1,'en_cours'=>2,'livree'=>3,'terminee'=>4];
                $currentStep = $statusOrder[$order->status] ?? -1;
            @endphp

            @if(!in_array($order->status, ['annulee','litige']))
                <div class="mb-4 d-flex align-items-center" style="overflow-x:auto;padding:4px 0">
                    @foreach($steps as $i => $step)
                        <div class="text-center" style="min-width:72px;flex:1">
                            <div class="mx-auto mb-1 rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;
                                        background:{{ $i <= $currentStep ? '#C4622D' : '#ECD8C6' }};
                                        color:{{ $i <= $currentStep ? '#fff' : '#9A8070' }}">
                                <i class="bi {{ $step['icon'] }}" style="font-size:.85rem"></i>
                            </div>
                            <div style="font-size:.68rem;color:{{ $i <= $currentStep ? '#C4622D' : '#9A8070' }};
                                        font-weight:{{ $i === $currentStep ? '700' : '400' }}">
                                {{ $step['label'] }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div style="flex:1;height:2px;min-width:12px;margin-bottom:20px;
                                        background:{{ $i < $currentStep ? '#C4622D' : '#ECD8C6' }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($order->status === 'annulee')
                <div class="mb-3 alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i><strong>Commande annulée</strong>
                    @if($order->cancellation_reason || $order->rejection_reason)
                        <div class="mt-2 small"><strong>Motif :</strong> {{ $order->cancellation_reason ?? $order->rejection_reason }}</div>
                    @endif
                </div>
            @endif

            @if($order->status === 'litige')
                <div class="mb-3 alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Litige en cours</strong> — Notre équipe va intervenir sous 48h.
                    @if($order->admin_note)
                        <hr class="my-2">
                        <strong>Décision admin :</strong> {{ $order->admin_note }}
                    @endif
                </div>
            @endif

            <hr style="border-color:#ECD8C6">

            <h6 class="mb-2 fw-700">Description de la commande</h6>
            <p class="mb-3 text-muted">{{ $order->description }}</p>

            @if($order->images->isNotEmpty())
                <h6 class="mb-2 fw-700">Photos du projet</h6>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach($order->images as $image)
                        <a href="{{ route('files.order-image', [$order, $image]) }}" target="_blank">
                            <img src="{{ route('files.order-image', [$order, $image]) }}" alt="Photo du projet"
                                 class="rounded-3" style="width:110px;height:90px;object-fit:cover">
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="row g-3">
                @if($order->budget)
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background:#F5EFE6">
                            <div style="font-size:.72rem;color:#9A8070;text-transform:uppercase;letter-spacing:.1em">Budget</div>
                            <div class="mt-1 fw-700 text-clay">{{ number_format($order->budget,0,',',' ') }} XOF</div>
                        </div>
                    </div>
                @endif
                @if($order->deadline)
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background:#F5EFE6">
                            <div style="font-size:.72rem;color:#9A8070;text-transform:uppercase;letter-spacing:.1em">Délai</div>
                            <div class="mt-1 fw-700">{{ $order->deadline->format('d/m/Y') }}</div>
                        </div>
                    </div>
                @endif
                @if($order->contract_path)
                    <div class="col-md-4">
                        <a href="{{ route('files.contract', $order) }}" target="_blank"
                           class="p-3 rounded-3 d-block text-decoration-none" style="background:#F5EFE6">
                            <div style="font-size:.72rem;color:#9A8070;text-transform:uppercase;letter-spacing:.1em">Contrat</div>
                            <div class="mt-1 fw-700 text-clay">
                                <i class="bi bi-file-earmark-text me-1"></i>Voir le contrat
                            </div>
                        </a>
                        <a href="{{ route('files.contract.download', $order) }}" class="small text-clay mt-2 d-inline-block">Télécharger le PDF</a>
                    </div>
                @endif
                @if($order->completion_photo_path)
                    <div class="col-md-4">
                        <a href="{{ route('files.completion', $order) }}" target="_blank"
                           class="p-3 rounded-3 d-block text-decoration-none" style="background:#F5EFE6">
                            <div style="font-size:.72rem;color:#9A8070;text-transform:uppercase;letter-spacing:.1em">Preuve</div>
                            <div class="mt-1 fw-700 text-clay"><i class="bi bi-image me-1"></i>Voir la preuve</div>
                        </a>
                    </div>
                @endif
            </div>

            {{-- ── DEVIS NÉGOCIÉ ── --}}
            @if($order->negotiation_status !== 'none' || $order->canProposeQuote())
                <hr style="border-color:#ECD8C6">
                <h6 class="mb-2 fw-700"><i class="bi bi-chat-left-text me-2 text-clay"></i>Négociation du prix</h6>

                @if($order->quotes->isNotEmpty())
                    <div class="mb-3">
                        @foreach($order->quotes->sortBy('round') as $quote)
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-2"
                                 style="background:{{ $quote->proposed_by_role === 'artisan' ? '#F5EFE6' : '#EFF6FF' }}">
                                <div>
                                    <span class="fw-700" style="font-size:.88rem">{{ $quote->formattedAmount() }}</span>
                                    <span class="text-muted ms-2" style="font-size:.75rem">
                                        proposé par {{ $quote->proposed_by_role === 'artisan' ? 'vous' : $order->client->name }}
                                    </span>
                                    @if($quote->message)
                                        <div class="text-muted" style="font-size:.8rem">« {{ $quote->message }} »</div>
                                    @endif
                                    @if($quote->price_anomaly_note)
                                        <div class="mt-1" style="font-size:.75rem;color:#C0392B">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $quote->price_anomaly_note }}
                                        </div>
                                    @endif
                                </div>
                                <span class="badge" style="background:{{ match($quote->status){'pending'=>'#D4A853','accepted'=>'#2E7D32','rejected'=>'#C0392B',default=>'#9A8070'} }};color:#fff;font-size:.7rem">
                                    {{ match($quote->status){'pending'=>'En attente','accepted'=>'Accepté','rejected'=>'Refusé','countered'=>'Remplacé',default=>$quote->status} }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @php $currentQuote = $order->currentQuote(); @endphp

                @if($currentQuote && $currentQuote->proposed_by_id !== auth()->id())
                    <p class="text-muted mb-2" style="font-size:.78rem">
                        <i class="bi bi-clock me-1"></i>Expire le {{ $currentQuote->expires_at->format('d/m/Y à H:i') }} si aucune réponse.
                    </p>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <form action="{{ route('quotes.accept', [$order, $currentQuote]) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-clay btn-sm"
                                    onclick="return confirm('Accepter cette proposition à {{ $currentQuote->formattedAmount() }} ?')">
                                <i class="bi bi-check-lg me-1"></i>Accepter {{ $currentQuote->formattedAmount() }}
                            </button>
                        </form>
                        <form action="{{ route('quotes.reject', [$order, $currentQuote]) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Refuser</button>
                        </form>
                    </div>
                @endif

                @if(!$order->canProposeMoreQuotes() && $order->negotiation_status === 'in_progress')
                    <p class="text-muted mb-2" style="font-size:.8rem">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Nombre maximum de propositions atteint ({{ \App\Models\Quote::MAX_ROUNDS }}). Acceptez la dernière offre ou annulez la commande.
                    </p>
                @elseif($order->canProposeMoreQuotes() && !($currentQuote && $currentQuote->proposed_by_id === auth()->id()))
                    <form action="{{ route('quotes.store', $order) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-end">
                        @csrf
                        <div>
                            <label class="form-label fw-600" style="font-size:.8rem">
                                {{ $currentQuote ? 'Contre-proposer' : 'Proposer un montant (XOF)' }}
                            </label>
                            <input type="number" name="amount" class="form-control" min="100" required
                                   style="width:160px" placeholder="Ex: 15000">
                        </div>
                        <div class="flex-grow-1" style="min-width:200px">
                            <label class="form-label fw-600" style="font-size:.8rem">Message (optionnel)</label>
                            <input type="text" name="message" class="form-control" maxlength="500"
                                   placeholder="Ex: Le travail nécessite plus de matériel">
                        </div>
                        <button type="submit" class="btn btn-outline-clay">
                            <i class="bi bi-send me-1"></i>Envoyer
                        </button>
                    </form>
                @endif
            @endif

            {{-- Actions artisan --}}
<div class="flex-wrap gap-2 mt-4 d-flex action-buttons-row">

    {{-- ① ACCEPTER (statut : en_attente) --}}
    @if($order->canBeAccepted())
        @php
            $paymentOk = $order->payment && $order->payment->status === 'completed';
        @endphp

        @if(!$paymentOk)
            <div class="alert alert-warning w-100 mb-2 py-2" style="font-size:.85rem">
                <i class="bi bi-hourglass-split me-2"></i>
                Le paiement du client n'est pas encore confirmé. Vous pourrez accepter dès que le paiement sera validé.
            </div>
        @endif

        <form action="{{ route('artisan.orders.accept', $order) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-clay w-100 w-sm-auto"
                    {{ !$paymentOk ? 'disabled' : '' }}
                    onclick="return confirm('Accepter cette commande ?')">
                <i class="bi bi-check-lg me-2"></i>Accepter la commande
            </button>
        </form>
        <form action="{{ route('artisan.orders.reject', $order) }}" method="POST">
            @csrf @method('PATCH')
            <textarea name="reason" class="mb-2 form-control" rows="2" minlength="5" maxlength="500"
                      placeholder="Motif du refus (5 caractères minimum)." required></textarea>
            <button type="submit" class="btn btn-outline-danger w-100 w-sm-auto"
                    onclick="return confirm('Refuser cette commande ?')">
                <i class="bi bi-x-lg me-2"></i>Refuser
            </button>
        </form>
    @endif

    {{-- ② DÉMARRER LE TRAVAIL (statut : acceptee) --}}
    @if($order->canBeStarted())
        <div class="mb-0 alert alert-info w-100 action-alert-row">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                <strong>Commande acceptée</strong> — Cliquez sur "Démarrer" quand vous commencez le travail.
            </div>
            <form action="{{ route('artisan.orders.start', $order) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-primary w-100 w-sm-auto"
                        onclick="return confirm('Démarrer le travail sur cette commande ?')">
                    <i class="bi bi-tools me-2"></i>Démarrer le travail
                </button>
            </form>
        </div>
    @endif

    {{-- ③ MARQUER COMME LIVRÉ (statut : en_cours) --}}
    @if($order->canBeDelivered())
        <div class="mb-0 alert w-100 action-alert-row"
             style="background:#D4EDDA;border-color:#C3E6CB">
            <div style="color:#155724">
                <i class="bi bi-tools me-2"></i>
                <strong>Travail en cours</strong> — Cliquez sur "Livrer" une fois le travail terminé.
            </div>
            <button type="button" class="btn btn-success w-100 w-sm-auto"
                    data-bs-toggle="modal" data-bs-target="#deliverModal">
                <i class="bi bi-truck me-2"></i>Marquer comme livrée
            </button>
        </div>
    @endif

    {{-- ④ EN ATTENTE DE VALIDATION CLIENT (statut : livree) --}}
    @if($order->status === \App\Models\Order::STATUS_DELIVERED)
        <div class="mb-0 alert alert-warning w-100">
            <i class="bi bi-hourglass-split me-2"></i>
            <strong>En attente du client</strong> — Le client doit valider la livraison pour déclencher le paiement.
            <br><small class="text-muted">Si le client ne valide pas sous 72h, contactez le support.</small>
        </div>
    @endif

    {{-- ⑤ TERMINÉE — répondre à l'avis --}}
    @if($order->status === \App\Models\Order::STATUS_COMPLETED && $order->review && !$order->review->artisan_reply)
        <button class="btn btn-outline-clay" data-bs-toggle="modal" data-bs-target="#replyModal">
            <i class="bi bi-reply me-2"></i>Répondre à l'avis client
        </button>
    @endif

</div>


        {{-- Bloc livraison --}}
        @if($order->needs_delivery)
        <div class="content-card mb-4" style="border:1px solid #ECD8C6">
            <h6 class="fw-700 mb-3"><i class="bi bi-bicycle me-2" style="color:#C4622D"></i>Livraison demandée</h6>
            @if($order->delivery)
                <div class="d-flex align-items-center gap-3">
                    <span class="badge px-3 py-2" style="background:{{ $order->delivery->statusColor() }};color:{{ $order->delivery->statusTextColor() }}">
                        {{ $order->delivery->statusLabel() }}
                    </span>
                    @if($order->delivery->livreur)
                        <div style="font-size:.85rem">
                            <strong>{{ $order->delivery->livreur->name }}</strong>
                            @if($order->delivery->livreur->phone)
                                — 📞 {{ $order->delivery->livreur->phone }}
                            @endif
                        </div>
                    @endif
                    @if($order->delivery->fee)
                        <span class="text-muted" style="font-size:.82rem">Frais : {{ number_format($order->delivery->fee,0,',',' ') }} XOF</span>
                    @endif
                </div>
                @if($order->delivery->isSearching() || $order->delivery->status === 'en_recherche')
                    <p class="text-muted mt-2 mb-0" style="font-size:.82rem">
                        <i class="bi bi-hourglass-split me-1"></i>Recherche d'un livreur à {{ $order->delivery_city ?? $order->client->city }}...
                    </p>
                @endif
            @else
                <p class="text-muted mb-0" style="font-size:.85rem">
                    <i class="bi bi-clock me-1"></i>La livraison sera déclenchée automatiquement au démarrage du travail.
                    Ville de livraison : <strong>{{ $order->delivery_city ?? $order->client->city }}</strong>
                </p>
            @endif
        </div>
        @endif

        @include('partials.delivery-tracking')

        {{-- Messagerie --}}
        @if(in_array($order->status, ['acceptee','en_cours','livree']))
            <div class="content-card">
                <div class="mb-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-700"><i class="bi bi-chat me-2 text-clay"></i>Messagerie</h5>
                    <a href="{{ route('messages.index', $order) }}" class="btn btn-sm btn-outline-clay">
                        <i class="bi bi-mic me-1"></i>Chat complet (vocal, traduction)
                    </a>
                </div>

                {{-- Messages --}}
                <div id="chat-box" style="max-height:340px;overflow-y:auto;padding:4px 0">
                    @php
                        // Le mini-chat de la fiche commande ne montre que la
                        // conversation privée client↔artisan (jamais les
                        // échanges avec le livreur).
                        $miniChatMsgs = $order->messages->filter(fn($m) =>
                            in_array($m->sender_id, [$order->client_id, $order->artisan_id])
                            && in_array($m->recipient_id, [$order->client_id, $order->artisan_id])
                        );
                    @endphp
                    @forelse($miniChatMsgs as $msg)
                        <div class="d-flex gap-2 mb-3 {{ $msg->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <img src="{{ $msg->sender->avatarUrl() }}" class="rounded-circle"
                                 width="34" height="34"
                                 style="object-fit:cover;flex-shrink:0;align-self:flex-end">
                            <div style="max-width:72%">
                                <div class="px-3 py-2 rounded-3"
                                     style="background:{{ $msg->sender_id === auth()->id() ? '#C4622D' : '#F5EFE6' }};
                                            color:{{ $msg->sender_id === auth()->id() ? '#fff' : '#2C1A0E' }}">
                                    @if($msg->body)
                                        <p class="mb-1" style="font-size:.9rem;margin:0">{{ $msg->body }}</p>
                                        @if($msg->sender_id !== auth()->id() && $msg->translated_body)
                                            <p class="mb-1" style="font-size:.78rem;font-style:italic;opacity:.85">
                                                <i class="bi bi-translate me-1"></i>{{ $msg->translated_body }}
                                            </p>
                                        @endif
                                    @endif
                                    @if($msg->isAudio())
                                        <audio controls preload="none" style="max-width:200px;height:32px;display:block;margin-top:4px">
                                            <source src="{{ route('files.message', [$msg, 'audio']) }}">
                                        </audio>
                                    @endif
                                    @if($msg->attachment_path)
                                        <a href="{{ route('files.message', [$msg, 'attachment']) }}" target="_blank"
                                           style="color:{{ $msg->sender_id === auth()->id() ? '#FFD0B0' : '#C4622D' }};font-size:.82rem">
                                            <i class="bi bi-paperclip me-1"></i>Pièce jointe
                                        </a>
                                    @endif
                                </div>
                                <div class="mt-1 text-muted"
                                     style="font-size:.7rem;text-align:{{ $msg->sender_id === auth()->id() ? 'right' : 'left' }}">
                                    {{ $msg->created_at->format('d/m H:i') }}
                                    @if($msg->sender_id === auth()->id() && $msg->read_at)
                                        <i class="bi bi-check2-all ms-1" style="color:#4CAF50"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-3 text-center text-muted" style="font-size:.875rem">
                            <i class="mb-2 bi bi-chat d-block" style="font-size:1.5rem;opacity:.3"></i>
                            Aucun message — commencez la conversation avec le client
                        </p>
                    @endforelse
                </div>

                <hr style="border-color:#ECD8C6">

                {{-- Formulaire envoi --}}
                <form action="{{ route('messages.store', [$order, $order->client]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="gap-2 d-flex chat-send-row">
                        <input type="text" name="body" class="form-control"
                               placeholder="Votre message au client..." style="font-size:.9rem">
                        <label class="btn btn-outline-secondary" title="Joindre un fichier">
                            <i class="bi bi-paperclip"></i>
                            <input type="file" name="attachment" class="d-none"
                                   accept="image/jpeg,image/png,image/webp,application/pdf">
                        </label>
                        <button type="submit" class="px-3 btn btn-clay">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    {{-- ── Colonne latérale ── --}}
    <div class="col-lg-4">

        {{-- Client --}}
        <div class="mb-4 text-center content-card">
            <p class="mb-3 section-label">CLIENT</p>
            <img src="{{ $order->client->avatarUrl() }}" class="mb-2 rounded-circle"
                 width="64" height="64"
                 style="object-fit:cover;border:2px solid #C4622D">
            <h6 class="mb-0 fw-700">{{ $order->client->name }}</h6>
            <p class="mb-0 text-muted" style="font-size:.85rem">📍 {{ $order->client->city }}</p>
            @if($order->client->phone)
                <p class="mb-0 text-muted" style="font-size:.82rem">
                    📞 {{ $order->client->phone }}
                </p>
            @endif
            <p class="mt-1 mb-0 text-muted" style="font-size:.78rem">
                📧 {{ $order->client->email }}
            </p>
        </div>

        {{-- Paiement --}}
        @if($order->payment)
            <div class="mb-4 content-card" style="background:#F5EFE6">
                <p class="mb-3 section-label">PAIEMENT</p>
                <div class="mb-2 d-flex justify-content-between" style="font-size:.875rem">
                    <span class="text-muted">Montant total</span>
                    <strong>{{ number_format($order->payment->amount,0,',',' ') }} XOF</strong>
                </div>
                <div class="mb-2 d-flex justify-content-between" style="font-size:.875rem">
                    <span class="text-muted">Commission (10%)</span>
                    <span style="color:#721C24">- {{ number_format($order->payment->commission,0,',',' ') }} XOF</span>
                </div>
                <hr style="border-color:#ECD8C6">
                <div class="d-flex justify-content-between" style="font-size:.95rem">
                    <strong>Net à recevoir</strong>
                    <strong class="text-clay">{{ number_format($order->payment->net_amount,0,',',' ') }} XOF</strong>
                </div>
                <div class="mt-2 text-center">
                    <span class="badge-status status-{{ $order->payment->status === 'completed' ? 'terminee' : 'en_attente' }}">
                        {{ $order->payment->status === 'completed' ? 'Payé ✅' : 'En attente' }}
                    </span>
                </div>
            </div>
        @endif

        {{-- Avis reçu --}}
        @if($order->review)
            <div class="content-card" style="background:#F5EFE6">
                <p class="mb-2 section-label">AVIS CLIENT</p>
                <div style="color:#D4A853;font-size:1.2rem">
                    @for($i=1;$i<=5;$i++)
                        <i class="bi bi-star{{ $i <= $order->review->rating ? '-fill' : '' }}"></i>
                    @endfor
                    <span class="ms-1 fw-700" style="font-size:.9rem">{{ $order->review->rating }}/5</span>
                </div>
                @if($order->review->comment)
                    <p class="mt-2 mb-2" style="font-size:.875rem;color:#5C3D1E">
                        "{{ $order->review->comment }}"
                    </p>
                @endif
                @if($order->review->artisan_reply)
                    <div class="p-2 rounded" style="background:#ECD8C6;font-size:.8rem">
                        <strong style="color:#C4622D">Votre réponse :</strong>
                        {{ $order->review->artisan_reply }}
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>

{{-- Modal marquer comme livré (photo obligatoire) --}}
@if($order->canBeDelivered())
    <div class="modal fade" id="deliverModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('artisan.orders.deliver', $order) }}" method="POST"
                      enctype="multipart/form-data" id="deliver-form">
                    @csrf @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title fw-700">Marquer comme livrée</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted" style="font-size:.875rem">
                            Une photo du travail terminé est obligatoire. Le client sera notifié
                            pour valider et payer.
                        </p>
                        <label class="form-label fw-600" style="font-size:.85rem">
                            Photo du travail effectué <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="completion_photo" id="completion-photo-input"
                               class="form-control" accept="image/jpeg,image/png,image/webp" required
                               onchange="previewCompletionPhoto(this)">
                        <div class="form-text">JPG, PNG ou WebP — 5 Mo max</div>
                        <img id="completion-photo-preview" class="mt-3 rounded d-none"
                             style="max-width:100%;max-height:260px;object-fit:cover;border:1px solid #ECD8C6">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-truck me-2"></i>Confirmer la livraison
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Modal réponse à l'avis --}}
@if($order->review && !$order->review->artisan_reply)
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-700">Répondre à l'avis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reviews.reply', $order->review) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body">
                        <div class="p-3 mb-3 rounded" style="background:#F5EFE6">
                            <div style="color:#D4A853">
                                @for($i=1;$i<=5;$i++)
                                    <i class="bi bi-star{{ $i<=$order->review->rating?'-fill':'' }}"></i>
                                @endfor
                            </div>
                            <p class="mt-1 mb-0" style="font-size:.875rem">{{ $order->review->comment }}</p>
                        </div>
                        <textarea name="reply" class="form-control" rows="3"
                                  placeholder="Votre réponse publique (max 300 caractères)..."
                                  maxlength="300" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-clay">Publier la réponse</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    // Scroll automatique vers le bas du chat
    const chatBox = document.getElementById('chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

    // Aperçu de la photo de fin de travaux avant envoi
    function previewCompletionPhoto(input) {
        const preview = document.getElementById('completion-photo-preview');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('d-none');
        }
    }

    // Ouvre automatiquement la modal "Marquer comme livrée" si on arrive
    // depuis le bouton "Livrer" de la liste des commandes.
    if (sessionStorage.getItem('open_deliver_modal') === '1') {
        sessionStorage.removeItem('open_deliver_modal');
        const deliverModalEl = document.getElementById('deliverModal');
        if (deliverModalEl && window.bootstrap) {
            new bootstrap.Modal(deliverModalEl).show();
        }
    }
</script>
@endpush

@push('styles')
<style>
    /* Boutons d'action pleine largeur sur mobile, largeur auto dès 576px */
    @media (max-width: 575.98px) {
        .action-buttons-row form { width: 100%; }
    }
    @media (min-width: 576px) {
        .btn.w-sm-auto { width: auto !important; }
    }

    /* Blocs "Démarrer"/"Livrer" : texte + bouton côte à côte sur tablette+,
       empilés proprement sur mobile au lieu d'être compressés */
    .action-alert-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    @media (max-width: 575.98px) {
        .action-alert-row { flex-direction: column; align-items: stretch; }
        .action-alert-row form { width: 100%; }
    }

    /* Formulaire d'envoi de message : reste sur une ligne dès que possible,
       mais l'input peut rétrécir sans faire déborder les boutons */
    .chat-send-row { flex-wrap: nowrap; }
    .chat-send-row input.form-control { min-width: 0; }
    @media (max-width: 400px) {
        .chat-send-row label.btn { padding-left: 10px; padding-right: 10px; }
    }

    /* Bulles de chat plus larges sur petit écran pour limiter les retours à la ligne inutiles */
    @media (max-width: 575.98px) {
        #chat-box [style*="max-width:72%"] { max-width: 85% !important; }
    }

    /* Timeline de statut : icônes et texte un peu plus compacts sur mobile */
    @media (max-width: 575.98px) {
        .badge-status.px-3.py-2 { font-size: .72rem; padding: 4px 10px !important; }
    }
</style>
@endpush
@endsection
