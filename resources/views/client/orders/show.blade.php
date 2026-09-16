@extends('layouts.dashboard')
@section('title', 'Commande #'.$order->id)
@section('page-title', 'Commande #'.$order->id)

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('client.dashboard') }}" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="{{ route('client.orders.index') }}" class="sidebar-link active">
        <i class="bi bi-bag"></i> Mes commandes
    </a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <div class="mt-2 sidebar-section-title">Découvrir</div>
    <a href="{{ route('artisans.index') }}" class="sidebar-link">
        <i class="bi bi-search"></i> Explorer
    </a>
@endsection

@section('topbar-actions')
    <a href="{{ route('client.orders.index') }}" class="btn btn-sm btn-outline-clay">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
@endsection

@section('content')
<div class="row g-4">

    {{-- Alerte admin --}}
    @if($order->has_alert)
    <div class="col-12">
        <div class="alert mb-0 py-3" style="background:#fff3cd;border:2px solid #ffc107;border-radius:12px">
            <div class="d-flex gap-3 align-items-start">
                <i class="bi bi-shield-exclamation" style="font-size:1.8rem;color:#856404;flex-shrink:0"></i>
                <div>
                    <p class="fw-700 mb-1" style="color:#856404">⚠️ Alerte de sécurité — Équipe ArtisanHub</p>
                    <p class="mb-0" style="font-size:.88rem;color:#5C3D1E">{{ $order->alert_message }}</p>
                    <p class="mb-0 mt-1 text-muted" style="font-size:.75rem">Reçue le {{ $order->alerted_at?->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

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

            {{-- ══ TIMELINE ══ --}}
            @php
                $steps = [
                    ['status' => 'en_attente', 'label' => 'Demande envoyée',   'icon' => 'bi-send'],
                    ['status' => 'acceptee',   'label' => 'Acceptée',          'icon' => 'bi-check-circle'],
                    ['status' => 'en_cours',   'label' => 'Travail en cours',  'icon' => 'bi-tools'],
                    ['status' => 'livree',     'label' => 'Livrée',            'icon' => 'bi-truck'],
                    ['status' => 'terminee',   'label' => 'Terminée & payée',  'icon' => 'bi-star-fill'],
                ];
                $statusOrder = [
                    'en_attente' => 0,
                    'acceptee'   => 1,
                    'en_cours'   => 2,
                    'livree'     => 3,
                    'terminee'   => 4,
                ];
                $currentStep = $statusOrder[$order->status] ?? -1;
            @endphp

            @if(!in_array($order->status, ['annulee', 'litige']))
                <div class="mb-4 d-flex align-items-center" style="overflow-x:auto;padding:4px 0">
                    @foreach($steps as $i => $step)
                        <div class="flex-shrink-0 text-center" style="min-width:80px;flex:1">
                            <div class="mx-auto mb-1 rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:38px;height:38px;
                                        background:{{ $i <= $currentStep ? '#C4622D' : '#ECD8C6' }};
                                        color:{{ $i <= $currentStep ? '#fff' : '#9A8070' }}">
                                <i class="bi {{ $step['icon'] }}" style="font-size:.9rem"></i>
                            </div>
                            <div style="font-size:.68rem;
                                        color:{{ $i <= $currentStep ? '#C4622D' : '#9A8070' }};
                                        font-weight:{{ $i === $currentStep ? '700' : '400' }}">
                                {{ $step['label'] }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div style="flex:1;min-width:16px;height:2px;margin-bottom:20px;
                                        background:{{ $i < $currentStep ? '#C4622D' : '#ECD8C6' }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Alertes spéciales --}}
            @if($order->status === 'annulee')
                <div class="mb-3 alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i><strong>Commande annulée.</strong>
                    @if($order->cancellation_reason || $order->rejection_reason)
                        <div class="mt-2 small"><strong>Motif :</strong> {{ $order->cancellation_reason ?? $order->rejection_reason }}</div>
                    @endif
                </div>
            @elseif($order->status === 'litige')
                <div class="mb-3 alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Litige en cours</strong> — Notre équipe intervient sous 48h.
                    @if($order->admin_note)
                        <hr class="my-2">
                        <strong>Décision :</strong> {{ $order->admin_note }}
                    @endif
                </div>
            @endif

            <hr style="border-color:#ECD8C6">

            <h6 class="mb-2 fw-700">Description</h6>
            <p class="text-muted">{{ $order->description }}</p>

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

            {{-- Détails budget / délai / contrat --}}
            <div class="row g-3">
                @if($order->budget)
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background:#F5EFE6">
                            <div style="font-size:.72rem;color:#9A8070;text-transform:uppercase;letter-spacing:.1em">Budget</div>
                            <div class="mt-1 fw-700 text-clay">
                                {{ number_format($order->budget, 0, ',', ' ') }} XOF
                            </div>
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
                           class="p-3 rounded-3 d-block text-decoration-none h-100" style="background:#F5EFE6">
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
                           class="p-3 rounded-3 d-block text-decoration-none h-100" style="background:#F5EFE6">
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
                                 style="background:{{ $quote->proposed_by_role === 'client' ? '#F5EFE6' : '#EFF6FF' }}">
                                <div>
                                    <span class="fw-700" style="font-size:.88rem">{{ $quote->formattedAmount() }}</span>
                                    <span class="text-muted ms-2" style="font-size:.75rem">
                                        proposé par {{ $quote->proposed_by_role === 'client' ? 'vous' : $order->artisan->name }}
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
                                {{ $currentQuote ? 'Contre-proposer' : 'Proposer un autre montant (XOF)' }}
                            </label>
                            <input type="number" name="amount" class="form-control" min="100" required
                                   style="width:160px" placeholder="Ex: 15000">
                        </div>
                        <div class="flex-grow-1" style="min-width:200px">
                            <label class="form-label fw-600" style="font-size:.8rem">Message (optionnel)</label>
                            <input type="text" name="message" class="form-control" maxlength="500">
                        </div>
                        <button type="submit" class="btn btn-outline-clay">
                            <i class="bi bi-send me-1"></i>Envoyer
                        </button>
                    </form>
                @endif
            @endif

            {{-- ══ ACTIONS CLIENT ══ --}}
            <div class="mt-4">

                {{-- EN ATTENTE / ACCEPTÉE → Annuler --}}
                @if($order->canBeCancelled())
                    <div class="p-3 mb-3 rounded-3"
                         style="background:#F5EFE6;border:1px solid #ECD8C6">
                        <p class="mb-2 fw-700 text-clay">
                           <i class="bi bi-x-circle me-2"></i>Vous pouvez annuler cette commande
                        </p>
                        <form action="{{ route('client.orders.cancel', $order) }}" method="POST">
                           @csrf @method('PATCH')
                           <textarea name="reason" class="mb-2 form-control" rows="2" minlength="5" maxlength="500"
                                     placeholder="Indiquez le motif de l'annulation (5 caractères minimum)." required></textarea>
                           <button type="submit" class="btn btn-outline-secondary">
                               <i class="bi bi-x-circle me-2"></i>Annuler la commande
                           </button>
                        </form>
                    </div>
                @endif

                {{-- LIVREE → Valider et payer --}}
                @if($order->canBeValidated())
                    <div class="p-3 mb-3 rounded-3"
                         style="background:#D4EDDA;border:1px solid #C3E6CB">
                        <p class="mb-2 fw-700" style="color:#155724">
                           <i class="bi bi-truck me-2"></i>L'artisan a livré votre commande !
                        </p>
                        <p class="mb-3" style="font-size:.875rem;color:#155724">
                           Si vous êtes satisfait, validez pour déclencher le paiement via Mobile Money.
                           Sinon, signalez un problème.
                        </p>
                        <div class="flex-wrap gap-2 d-flex">
                           <form action="{{ route('client.orders.validate', $order) }}" method="POST">
                               @csrf @method('PATCH')
                               <button type="submit" class="btn btn-success"
                                       onclick="return confirm('Valider la livraison et payer {{ number_format($order->budget ?? 0, 0, ',', ' ') }} XOF ?')">
                                   <i class="bi bi-check-circle me-2"></i>Valider et payer
                               </button>
                           </form>
                           <form action="{{ route('client.orders.dispute', $order) }}" method="POST">
                               @csrf @method('PATCH')
                               <button type="submit" class="btn btn-outline-danger"
                                       onclick="return confirm('Signaler un problème ?')">
                                   <i class="bi bi-flag me-2"></i>Signaler un problème
                               </button>
                           </form>
                        </div>
                    </div>
                @endif

                @if($order->delivery?->proof_code && !$order->delivery->hasDeliveryProof())
                    <div class="p-3 mb-3 rounded-3" style="background:#FFF3CD;border:1px solid #FFECB5">
                        <p class="mb-1 fw-700" style="color:#856404">
                            <i class="bi bi-shield-check me-2"></i>Code de remise
                        </p>
                        <p class="mb-2" style="font-size:.875rem;color:#856404">
                            Communiquez ce code au livreur uniquement lorsque vous recevez votre commande.
                        </p>
                        <strong style="font-size:1.6rem;letter-spacing:.25em;color:#2C1A0E">
                            {{ $order->delivery->proof_code }}
                        </strong>
                    </div>
                @endif

                @if($order->delivery?->status === 'recuperee' && !$order->delivery->hasDeliveryProof())
                    <div class="p-3 mb-3 rounded-3" style="background:#F5EFE6;border:1px solid #ECD8C6">
                        <p class="mb-1 fw-700 text-clay">
                            <i class="bi bi-pen me-2"></i>Signer la remise
                        </p>
                        <p class="mb-2" style="font-size:.875rem">
                            Signez dans le cadre ci-dessous pour confirmer la réception de votre commande.
                        </p>
                        <form method="POST" action="{{ route('client.orders.sign-delivery', $order) }}"
                              onsubmit="return submitDeliverySignature(this)">
                            @csrf
                            <canvas id="delivery-signature" width="600" height="180"
                                    class="w-100 border rounded bg-white"
                                    style="touch-action:none;max-width:600px"></canvas>
                            <input type="hidden" name="signature" id="delivery-signature-input">
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="clearDeliverySignature()">Effacer</button>
                                <button type="submit" class="btn btn-sm btn-clay">Enregistrer la signature</button>
                            </div>
                        </form>
                    </div>
                    <script>
                        (() => {
                            const canvas = document.getElementById('delivery-signature');
                            const context = canvas.getContext('2d');
                            let drawing = false;
                            const point = event => {
                                const rect = canvas.getBoundingClientRect();
                                return {
                                    x: (event.clientX - rect.left) * canvas.width / rect.width,
                                    y: (event.clientY - rect.top) * canvas.height / rect.height
                                };
                            };
                            canvas.addEventListener('pointerdown', event => {
                                drawing = true;
                                canvas.setPointerCapture(event.pointerId);
                                const p = point(event);
                                context.beginPath();
                                context.moveTo(p.x, p.y);
                            });
                            canvas.addEventListener('pointermove', event => {
                                if (!drawing) return;
                                const p = point(event);
                                context.lineWidth = 3;
                                context.lineCap = 'round';
                                context.strokeStyle = '#2C1A0E';
                                context.lineTo(p.x, p.y);
                                context.stroke();
                            });
                            canvas.addEventListener('pointerup', () => drawing = false);
                            window.clearDeliverySignature = () => context.clearRect(0, 0, canvas.width, canvas.height);
                            window.submitDeliverySignature = form => {
                                const blank = document.createElement('canvas');
                                blank.width = canvas.width;
                                blank.height = canvas.height;
                                if (canvas.toDataURL() === blank.toDataURL()) {
                                    alert('Veuillez signer avant de continuer.');
                                    return false;
                                }
                                form.querySelector('#delivery-signature-input').value = canvas.toDataURL('image/png');
                                return true;
                            };
                        })();
                    </script>
                @endif

                {{-- TERMINEE → laisser un avis --}}
                @if($order->status === \App\Models\Order::STATUS_COMPLETED && !$order->review)
                    <div class="p-3 rounded-3" style="background:#F5EFE6;border:1px solid #ECD8C6">
                        <p class="mb-2 fw-700 text-clay">
                            <i class="bi bi-star me-2"></i>Laissez un avis à {{ $order->artisan->name }}
                        </p>
                        <form action="{{ route('reviews.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <div class="gap-1 mb-3 d-flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <input type="radio" name="rating" value="{{ $i }}"
                                           id="r{{ $i }}" class="d-none" required>
                                    <label for="r{{ $i }}"
                                           style="font-size:2rem;cursor:pointer;color:#ccc;transition:.1s"
                                           class="star-rate">★</label>
                                @endfor
                            </div>
                            <textarea name="comment" class="mb-3 form-control" rows="2"
                                      placeholder="Votre commentaire..." style="font-size:.875rem"></textarea>
                            <button type="submit" class="btn btn-clay btn-sm">
                                <i class="bi bi-send me-1"></i>Publier l'avis
                            </button>
                        </form>
                    </div>
                @endif

                {{-- GARANTIE "SATISFAIT OU REPRIS" --}}
                @if($order->status === \App\Models\Order::STATUS_COMPLETED && $order->payment && $order->payment->guarantee_contribution > 0 && $order->payment->paid_at && $order->payment->paid_at->diffInDays(now()) <= 14)
                    <div class="mt-3 p-3 rounded-3" style="background:#F5EFE6;border:1px solid #ECD8C6">

                        @if($order->guaranteeClaim)
                            @php $claim = $order->guaranteeClaim; @endphp
                            <p class="mb-2 fw-700 text-clay">
                                <i class="bi bi-shield-check me-2"></i>Réclamation garantie
                            </p>
                            <span class="badge-status status-{{ $claim->status === 'pending' ? 'en_attente' : ($claim->status === 'approved' ? 'terminee' : 'annulee') }}">
                                {{ ['pending'=>'En attente de traitement','approved'=>'Approuvée','rejected'=>'Rejetée'][$claim->status] }}
                            </span>
                            @if($claim->status === 'approved')
                                <p class="mt-2 mb-0" style="font-size:.85rem">
                                    Remboursement validé : <strong>{{ number_format($claim->refund_amount,0,',',' ') }} XOF</strong>
                                </p>
                            @endif
                            @if($claim->admin_note)
                                <p class="mt-2 mb-0 text-muted" style="font-size:.82rem">
                                    <strong>Réponse de l'équipe :</strong> {{ $claim->admin_note }}
                                </p>
                            @endif
                        @else
                            <p class="mb-2 fw-700 text-clay">
                                <i class="bi bi-shield-check me-2"></i>Cette commande est couverte par la garantie "satisfait ou repris"
                            </p>
                            <p class="mb-3 text-muted" style="font-size:.82rem">
                                En cas de malfaçon constatée après coup, vous pouvez signaler un problème sous 14 jours après le paiement.
                            </p>
                            <button type="button" class="btn btn-outline-clay btn-sm" data-bs-toggle="modal" data-bs-target="#guaranteeClaimModal">
                                <i class="bi bi-flag me-1"></i>Faire une réclamation garantie
                            </button>

                            <div class="modal fade" id="guaranteeClaimModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-700">Réclamation garantie — Commande #{{ $order->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('client.guarantee-claims.store', $order) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-600">Décrivez le problème <span class="text-danger">*</span></label>
                                                    <textarea name="reason" class="form-control" rows="3"
                                                              placeholder="Ex : malfaçon constatée, travail non conforme..." required maxlength="1000"></textarea>
                                                </div>
                                                <div class="mb-1">
                                                    <label class="form-label fw-600">Photo à l'appui (optionnelle)</label>
                                                    <input type="file" name="evidence" class="form-control" accept="image/jpeg,image/png,image/webp">
                                                    <small class="text-muted" style="font-size:.75rem">JPG, PNG ou WEBP — 5 Mo max.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-clay">Envoyer la réclamation</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- AVIS DÉJÀ DONNÉ --}}
                @if($order->review)
                    <div class="p-3 rounded-3" style="background:#F5EFE6">
                        <p class="mb-2 section-label">VOTRE AVIS</p>
                        <div style="color:#D4A853;font-size:1.1rem">
                            @for($i=1;$i<=5;$i++)
                                <i class="bi bi-star{{ $i <= $order->review->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        @if($order->review->comment)
                            <p class="mt-2 mb-0" style="font-size:.875rem">
                                "{{ $order->review->comment }}"
                            </p>
                        @endif
                        @if($order->review->artisan_reply)
                            <div class="p-2 mt-2 rounded" style="background:#ECD8C6;font-size:.82rem">
                                <strong style="color:#C4622D">Réponse de l'artisan :</strong>
                                {{ $order->review->artisan_reply }}
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>

        @include('partials.delivery-tracking')

        {{-- Messagerie --}}
        @if(in_array($order->status, ['acceptee', 'en_cours', 'livree']))
            <div class="content-card">
                <div class="mb-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-700">
                        <i class="bi bi-chat me-2 text-clay"></i>Messagerie
                    </h5>
                    <a href="{{ route('messages.index', $order) }}" class="btn btn-sm btn-outline-clay">
                        <i class="bi bi-mic me-1"></i>Chat complet (vocal, traduction)
                    </a>
                </div>

                <div id="chat-box" style="max-height:320px;overflow-y:auto;padding:4px 0">
                    @php
                        $miniChatMsgs = $order->messages->filter(fn($m) =>
                            in_array($m->sender_id, [$order->client_id, $order->artisan_id])
                            && in_array($m->recipient_id, [$order->client_id, $order->artisan_id])
                        );
                    @endphp
                    @forelse($miniChatMsgs as $msg)
                        @php $isMine = $msg->sender_id === auth()->id(); @endphp
                        <div class="d-flex gap-2 mb-3 {{ $isMine ? 'flex-row-reverse' : '' }}">
                            <img src="{{ $msg->sender->avatarUrl() }}" class="rounded-circle"
                                 width="32" height="32"
                                 style="object-fit:cover;flex-shrink:0;align-self:flex-end">
                            <div style="max-width:70%">
                                <div class="px-3 py-2 rounded-3"
                                     style="background:{{ $isMine ? '#C4622D' : '#F5EFE6' }};
                                            color:{{ $isMine ? '#fff' : '#2C1A0E' }}">
                                    @if($msg->body)
                                        <p style="font-size:.875rem;margin:0">{{ $msg->body }}</p>
                                        @if(!$isMine && $msg->translated_body)
                                            <p class="mt-1 mb-0" style="font-size:.78rem;font-style:italic;opacity:.85">
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
                                        <a href="{{ route('files.message', [$msg, 'attachment']) }}"
                                           target="_blank"
                                           style="font-size:.8rem;color:{{ $isMine ? '#FFD0B0' : '#C4622D' }}">
                                            <i class="bi bi-paperclip me-1"></i>Pièce jointe
                                        </a>
                                    @endif
                                </div>
                                <div class="mt-1 text-muted"
                                     style="font-size:.7rem;text-align:{{ $isMine ? 'right' : 'left' }}">
                                    {{ $msg->created_at->format('d/m H:i') }}
                                    @if($isMine && $msg->read_at)
                                        <i class="bi bi-check2-all ms-1" style="color:#4CAF50"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-3 text-center text-muted" style="font-size:.875rem">
                            <i class="mb-1 bi bi-chat d-block" style="font-size:1.5rem;opacity:.3"></i>
                            Aucun message — commencez la conversation
                        </p>
                    @endforelse
                </div>

                <hr style="border-color:#ECD8C6">

                <form action="{{ route('messages.store', [$order, $order->artisan]) }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="gap-2 d-flex">
                        <input type="text" name="body" class="form-control"
                               placeholder="Votre message..." style="font-size:.875rem"
                               autocomplete="off">
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

        {{-- Artisan --}}
        <div class="mb-4 text-center content-card">
            <p class="mb-3 section-label">ARTISAN</p>
            <img src="{{ $order->artisan->avatarUrl() }}" class="mb-2 rounded-circle"
                 width="64" height="64"
                 style="object-fit:cover;border:2px solid #C4622D">
            <h6 class="mb-0 fw-700">{{ $order->artisan->name }}</h6>
            <p class="mb-1 text-muted" style="font-size:.85rem">
                {{ $order->artisan->artisanProfile?->specialty }}
            </p>
            <p class="mb-3 text-muted" style="font-size:.82rem">
                📍 {{ $order->artisan->city }}
            </p>
            <a href="{{ route('artisans.show', $order->artisan->routeSlug()) }}"
               class="btn btn-sm btn-outline-clay w-100">
                Voir le profil
            </a>
        </div>

        {{-- Paiement --}}
        @if($order->payment)
            <div class="mb-4 content-card" style="background:#F5EFE6">
                <p class="mb-3 section-label">PAIEMENT</p>
                <div class="mb-2 d-flex justify-content-between" style="font-size:.875rem">
                    <span class="text-muted">Montant total</span>
                    <strong>{{ number_format($order->payment->amount, 0, ',', ' ') }} XOF</strong>
                </div>
                <div class="mb-2 d-flex justify-content-between" style="font-size:.875rem">
                    <span class="text-muted">Commission (10%)</span>
                    <span style="color:#721C24">
                        - {{ number_format($order->payment->commission, 0, ',', ' ') }} XOF
                    </span>
                </div>
                <hr style="border-color:#ECD8C6">
                <div class="d-flex justify-content-between" style="font-size:.9rem">
                    <strong>Net artisan</strong>
                    <strong class="text-clay">
                        {{ number_format($order->payment->net_amount, 0, ',', ' ') }} XOF
                    </strong>
                </div>
                <div class="mt-2 text-center">
                    <span class="badge-status {{ $order->payment->status === 'completed' ? 'status-terminee' : 'status-en_attente' }}">
                        {{ $order->payment->status === 'completed' ? '✅ Payé' : '⏳ En attente' }}
                    </span>
                </div>
            </div>
        @endif

        {{-- Guide étapes --}}
        @if(!in_array($order->status, ['terminee', 'annulee', 'litige']))
            <div class="content-card" style="background:#F5EFE6">
                <p class="mb-3 section-label">GUIDE</p>
                @php
                    $guide = [
                        'en_attente' => ['icon'=>'bi-hourglass','color'=>'#856404','bg'=>'#FFF3CD',
                            'text'=>'L\'artisan va répondre sous 48h. Vous recevrez un email.'],
                        'acceptee'   => ['icon'=>'bi-check-circle','color'=>'#055160','bg'=>'#CFF4FC',
                            'text'=>'Commande acceptée. L\'artisan va démarrer le travail.'],
                        'en_cours'   => ['icon'=>'bi-tools','color'=>'#004085','bg'=>'#CCE5FF',
                            'text'=>'L\'artisan travaille. Utilisez la messagerie pour communiquer.'],
                        'livree'     => ['icon'=>'bi-truck','color'=>'#155724','bg'=>'#D4EDDA',
                            'text'=>'Vérifiez le travail et validez pour payer l\'artisan.'],
                    ];
                    $step = $guide[$order->status] ?? null;
                @endphp
                @if($step)
                    <div class="gap-2 d-flex align-items-start">
                        <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:36px;height:36px;background:{{ $step['bg'] }}">
                            <i class="bi {{ $step['icon'] }}" style="color:{{ $step['color'] }}"></i>
                        </div>
                        <p class="mb-0" style="font-size:.85rem;color:{{ $step['color'] }}">
                            {{ $step['text'] }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script>
    const chatBox = document.getElementById('chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

    document.querySelectorAll('.star-rate').forEach((star, i, stars) => {
        star.addEventListener('click', () => {
            stars.forEach((s, j) => s.style.color = j <= i ? '#D4A853' : '#ccc');
        });
        star.addEventListener('mouseover', () => {
            stars.forEach((s, j) => s.style.color = j <= i ? '#D4A853' : '#ccc');
        });
    });
</script>
@endpush
