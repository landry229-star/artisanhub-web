@extends('layouts.app')
@section('title', 'Comment ça marche')
@section('meta_description', "Découvrez comment ArtisanHub fonctionne : trouvez un artisan, négociez le prix, commandez en toute sécurité et bénéficiez de la garantie satisfait ou repris.")

@section('content')

{{-- Hero --}}
<div style="background:linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 55%,#9E4A1E 100%);padding:40px 16px 32px">
    <div class="container text-center px-2">
        <p class="section-label mb-2" style="color:#E8845A">GUIDE</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,6vw,2.8rem);color:#fff;font-weight:800;max-width:600px;margin:0 auto 16px;line-height:1.25">
            Comment fonctionne ArtisanHub ?
        </h1>
        <p style="color:#C8B5A0;max-width:480px;margin:0 auto;font-size:clamp(.9rem,2.5vw,1rem)">
            De la recherche d'un artisan jusqu'à la livraison de votre commande,
            tout se passe en quelques étapes simples.
        </p>
    </div>
</div>

{{-- Tabs navigation --}}
<div class="sticky-top" style="background:#F5EFE6;border-bottom:1px solid #ECD8C6;top:56px;z-index:100">
    <div class="container px-0 px-sm-3">
        <ul class="nav nav-pills gap-2 py-3 px-3 px-sm-0 flex-nowrap overflow-auto how-tabs-scroll" id="howTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-600 text-nowrap" id="tab-client" data-bs-toggle="pill"
                    data-bs-target="#pane-client" type="button" role="tab"
                    style="color:#C4622D;background:#FFF0E8;border:1px solid #FFCBA4">
                    <i class="bi bi-person me-1"></i>Je suis client
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-600 text-nowrap" id="tab-artisan" data-bs-toggle="pill"
                    data-bs-target="#pane-artisan" type="button" role="tab">
                    <i class="bi bi-tools me-1"></i>Je suis artisan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-600 text-nowrap" id="tab-payment" data-bs-toggle="pill"
                    data-bs-target="#pane-payment" type="button" role="tab">
                    <i class="bi bi-credit-card me-1"></i>Paiements & livraison
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-600 text-nowrap" id="tab-faq" data-bs-toggle="pill"
                    data-bs-target="#pane-faq" type="button" role="tab">
                    <i class="bi bi-question-circle me-1"></i>FAQ
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
<div class="tab-content">

    {{-- ── ONGLET CLIENT ──────────────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="pane-client" role="tabpanel">
        <div class="text-center mb-4 mb-md-5">
            <p class="section-label">POUR LES CLIENTS</p>
            <h2 class="section-title">Commandez en 4 étapes</h2>
        </div>

        <div class="row g-3 g-md-4 mb-4 mb-md-5">
            @php
            $steps = [
                ['num'=>'01','icon'=>'bi-search','title'=>'Trouvez un artisan',
                 'desc'=>'Utilisez la recherche pour filtrer par spécialité, ville ou disponibilité. Consultez les profils, portfolios et avis des autres clients.'],
                ['num'=>'02','icon'=>'bi-chat-dots','title'=>'Discutez de votre projet',
                 'desc'=>"Envoyez un message directement à l'artisan pour décrire votre besoin, négocier le prix et définir les délais avant de passer commande."],
                ['num'=>'03','icon'=>'bi-bag-check','title'=>'Passez commande & payez',
                 'desc'=>"Validez votre commande en ligne via FedaPay (Mobile Money, carte bancaire). Votre paiement est sécurisé et bloqué jusqu'à la livraison."],
                ['num'=>'04','icon'=>'bi-star','title'=>'Recevez & évaluez',
                 'desc'=>"Une fois votre commande livrée et validée, laissez un avis pour aider la communauté et débloquer le paiement de l'artisan."],
            ];
            @endphp

            @foreach($steps as $s)
            <div class="col-6 col-md-6 col-lg-3">
                <div class="card h-100 p-3 p-md-4 text-center">
                    <div style="font-size:1.6rem;background:var(--sand);border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <i class="bi {{ $s['icon'] }}" style="color:var(--clay)"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:.1em;color:var(--clay);margin-bottom:4px">ÉTAPE {{ $s['num'] }}</div>
                    <h5 class="fw-700 mb-2" style="font-size:1rem">{{ $s['title'] }}</h5>
                    <p class="text-muted mb-0" style="font-size:.85rem">{{ $s['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Protection acheteur --}}
        <div class="card p-3 p-md-4 mb-4" style="background:#F0FFF4;border-color:#A5D6A7">
            <div class="d-flex gap-3 align-items-start">
                <i class="bi bi-shield-check fs-3" style="color:#2E7D32;flex-shrink:0"></i>
                <div>
                    <h6 class="fw-700 mb-1" style="color:#1B5E20">Votre commande est protégée</h6>
                    <p class="mb-0 text-muted" style="font-size:.9rem">
                        Votre paiement est conservé en séquestre et ne sera libéré à l'artisan
                        qu'après votre validation de la livraison. En cas de litige, notre équipe
                        arbitre et peut déclencher un remboursement.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('artisans.index') }}" class="btn btn-clay px-4 px-md-5 py-2 w-100 w-sm-auto">
                <i class="bi bi-search me-2"></i>Trouver un artisan maintenant
            </a>
        </div>
    </div>

    {{-- ── ONGLET ARTISAN ─────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-artisan" role="tabpanel">
        <div class="text-center mb-4 mb-md-5">
            <p class="section-label">POUR LES ARTISANS</p>
            <h2 class="section-title">Développez votre activité</h2>
        </div>

        <div class="row g-3 g-md-4 mb-4 mb-md-5">
            @php
            $artisanSteps = [
                ['num'=>'01','icon'=>'bi-person-plus','title'=>'Créez votre profil',
                 'desc'=>"Inscrivez-vous, renseignez votre spécialité, ville et bio. Ajoutez des photos de vos créations dans votre portfolio pour mettre en valeur votre travail."],
                ['num'=>'02','icon'=>'bi-grid','title'=>'Publiez vos services',
                 'desc'=>"Créez des fiches de services avec prix, délai et description. Les clients peuvent vous trouver via la recherche et voir exactement ce que vous proposez."],
                ['num'=>'03','icon'=>'bi-clipboard-check','title'=>'Gérez vos commandes',
                 'desc'=>"Acceptez ou refusez les commandes depuis votre espace. Échangez avec le client, marquez la commande comme livrée une fois terminée."],
                ['num'=>'04','icon'=>'bi-wallet2','title'=>'Recevez votre paiement',
                 'desc'=>"Après validation par le client, votre paiement (moins la commission plateforme) est crédité sur votre portefeuille. Retirez vers Mobile Money quand vous voulez."],
            ];
            @endphp

            @foreach($artisanSteps as $s)
            <div class="col-6 col-md-6 col-lg-3">
                <div class="card h-100 p-3 p-md-4 text-center">
                    <div style="font-size:1.6rem;background:var(--sand);border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <i class="bi {{ $s['icon'] }}" style="color:var(--clay)"></i>
                    </div>
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:.1em;color:var(--clay);margin-bottom:4px">ÉTAPE {{ $s['num'] }}</div>
                    <h5 class="fw-700 mb-2" style="font-size:1rem">{{ $s['title'] }}</h5>
                    <p class="text-muted mb-0" style="font-size:.85rem">{{ $s['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Commission info --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card p-3 p-md-4 text-center h-100">
                    <i class="bi bi-percent fs-2 mb-2" style="color:var(--clay)"></i>
                    <h6 class="fw-700">Commission transparente</h6>
                    <p class="text-muted mb-0" style="font-size:.9rem">Un faible pourcentage prélevé uniquement sur les commandes complétées. Aucun frais d'inscription.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card p-3 p-md-4 text-center h-100">
                    <i class="bi bi-phone fs-2 mb-2" style="color:var(--clay)"></i>
                    <h6 class="fw-700">Retrait Mobile Money</h6>
                    <p class="text-muted mb-0" style="font-size:.9rem">Retirez vos gains directement sur MTN, Moov ou tout autre opérateur compatible FedaPay.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card p-3 p-md-4 text-center h-100">
                    <i class="bi bi-graph-up fs-2 mb-2" style="color:var(--clay)"></i>
                    <h6 class="fw-700">Statistiques détaillées</h6>
                    <p class="text-muted mb-0" style="font-size:.9rem">Suivez vos revenus, commandes et avis depuis votre tableau de bord artisan.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('register') }}" class="btn btn-clay px-4 px-md-5 py-2 w-100 w-sm-auto">
                <i class="bi bi-tools me-2"></i>Rejoindre en tant qu'artisan
            </a>
        </div>
    </div>

    {{-- ── ONGLET PAIEMENTS & LIVRAISON ───────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-payment" role="tabpanel">
        <div class="text-center mb-4 mb-md-5">
            <p class="section-label">PAIEMENTS & LIVRAISON</p>
            <h2 class="section-title">Sécurité & transparence</h2>
        </div>

        <div class="row g-4 mb-4 mb-md-5">
            <div class="col-12 col-lg-6">
                <h5 class="fw-700 mb-3"><i class="bi bi-credit-card me-2" style="color:var(--clay)"></i>Moyens de paiement acceptés</h5>
                <ul class="list-unstyled" style="font-size:.95rem">
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2E7D32"></i>Mobile Money (MTN, Moov…)</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2E7D32"></i>Carte bancaire Visa / Mastercard</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2E7D32"></i>Portefeuille FedaPay</li>
                </ul>
                <div class="card p-3 mt-3" style="background:#FFF0E8;border-color:#FFCBA4">
                    <p class="mb-0" style="font-size:.9rem;color:#8B3A1A">
                        <i class="bi bi-lock-fill me-1"></i>
                        Tous les paiements sont traités par <strong>FedaPay</strong>, une passerelle de paiement certifiée pour l'Afrique de l'Ouest. Vos données bancaires ne transitent jamais par nos serveurs.
                    </p>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h5 class="fw-700 mb-3"><i class="bi bi-truck me-2" style="color:var(--clay)"></i>Livraison</h5>
                <p class="text-muted" style="font-size:.95rem">
                    Selon les artisans et commandes, la livraison peut être assurée par notre réseau de livreurs partenaires ou directement par l'artisan.
                </p>
                <ul class="list-unstyled" style="font-size:.95rem">
                    <li class="mb-2"><i class="bi bi-geo-alt-fill me-2" style="color:var(--clay)"></i>Suivi en temps réel de votre colis</li>
                    <li class="mb-2"><i class="bi bi-bell-fill me-2" style="color:var(--clay)"></i>Notifications à chaque étape (assignation, en route, livré)</li>
                    <li class="mb-2"><i class="bi bi-shield-check-fill me-2" style="color:var(--clay)"></i>Signature électronique à la réception</li>
                </ul>
            </div>
        </div>

        {{-- Flux de fonds --}}
        <h5 class="fw-700 mb-3 text-center">Comment circule l'argent ?</h5>
        @php
        $flow = [
            ['icon'=>'bi-person','label'=>'Client paie','bg'=>null],
            ['icon'=>'bi-safe','label'=>'Séquestre ArtisanHub','bg'=>'#FFF0E8'],
            ['icon'=>'bi-truck','label'=>'Artisan livre','bg'=>null],
            ['icon'=>'bi-check2-circle','label'=>'Client valide','bg'=>null,'color'=>'#2E7D32'],
            ['icon'=>'bi-wallet2','label'=>'Artisan encaissé','bg'=>'#F0FFF4','color'=>'#2E7D32'],
        ];
        @endphp

        {{-- Mobile: vertical stack with down arrows --}}
        <div class="d-flex d-md-none flex-column align-items-center gap-2 mb-4">
            @foreach($flow as $i => $f)
                <div class="card p-3 w-100" style="max-width:280px;{{ $f['bg'] ? 'background:'.$f['bg'] : '' }}">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi {{ $f['icon'] }} fs-3" style="color:{{ $f['color'] ?? 'var(--clay)' }}"></i>
                        <div class="fw-600" style="font-size:.9rem">{{ $f['label'] }}</div>
                    </div>
                </div>
                @if(!$loop->last)
                    <i class="bi bi-arrow-down fs-4 text-muted"></i>
                @endif
            @endforeach
        </div>

        {{-- Desktop/tablet: horizontal row with right arrows --}}
        <div class="d-none d-md-flex align-items-center justify-content-center flex-wrap gap-2 mb-4">
            @foreach($flow as $i => $f)
                <div class="card p-3 text-center" style="width:140px;{{ $f['bg'] ? 'background:'.$f['bg'] : '' }}">
                    <i class="bi {{ $f['icon'] }} fs-2" style="color:{{ $f['color'] ?? 'var(--clay)' }}"></i>
                    <div class="fw-600 mt-1" style="font-size:.85rem">{{ $f['label'] }}</div>
                </div>
                @if(!$loop->last)
                    <i class="bi bi-arrow-right fs-3 text-muted"></i>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── ONGLET FAQ ──────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="pane-faq" role="tabpanel">
        <div class="text-center mb-4 mb-md-5">
            <p class="section-label">FAQ</p>
            <h2 class="section-title">Questions fréquentes</h2>
        </div>

        @php
        $faqs = [
            ['q'=>"L'inscription est-elle gratuite ?",
             'a'=>"Oui, totalement. Créer un compte client ou artisan ne coûte rien. Une commission est prélevée sur les commandes complétées pour faire fonctionner la plateforme."],
            ['q'=>"Comment puis-je contacter un artisan avant de commander ?",
             'a'=>"Rendez-vous sur le profil d'un artisan, puis cliquez sur « Contacter ». Vous pouvez échanger autant que nécessaire avant de vous engager."],
            ['q'=>"Que se passe-t-il si je ne suis pas satisfait de la commande ?",
             'a'=>"Vous pouvez ouvrir un litige depuis votre espace. Notre équipe examinera la situation et pourra décider d'un remboursement partiel ou total selon les cas."],
            ['q'=>"Comment les artisans reçoivent-ils leur paiement ?",
             'a'=>"Après la validation de la livraison par le client, le montant (commission déduite) est ajouté au portefeuille de l'artisan. Il peut ensuite effectuer un retrait vers Mobile Money."],
            ['q'=>"La livraison est-elle disponible partout au Bénin ?",
             'a'=>"Notre réseau de livreurs couvre progressivement plusieurs villes. Vérifiez la disponibilité lors de la création de votre commande. Certains artisans proposent aussi leur propre livraison."],
            ['q'=>"Mes données personnelles sont-elles protégées ?",
             'a'=>"Oui. Nous ne vendons aucune donnée à des tiers. Consultez notre politique de confidentialité pour tous les détails."],
        ];
        @endphp

        <div class="accordion" id="faqAccordion" style="max-width:720px;margin:0 auto">
            @foreach($faqs as $i => $faq)
            <div class="accordion-item border mb-2" style="border-radius:10px!important;overflow:hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fw-600"
                        type="button" data-bs-toggle="collapse"
                        data-bs-target="#faq-{{ $i }}"
                        style="{{ $i === 0 ? 'color:var(--clay);background:#FFF0E8' : '' }};font-size:.95rem">
                        {{ $faq['q'] }}
                    </button>
                </h2>
                <div id="faq-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                    data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted" style="font-size:.9rem">
                        {{ $faq['a'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">Vous avez une autre question ?</p>
            <a href="{{ route('support') }}" class="btn btn-outline-clay px-4 w-100 w-sm-auto">
                <i class="bi bi-envelope me-2"></i>Contacter le support
            </a>
        </div>
    </div>

</div>{{-- .tab-content --}}
</div>{{-- .container --}}

@push('styles')
<style>
    /* Barre d'onglets scrollable proprement sur mobile, sans scrollbar disgracieuse */
    .how-tabs-scroll {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .how-tabs-scroll::-webkit-scrollbar {
        height: 4px;
    }

    /* Evite que le contenu ne déborde horizontalement sur très petits écrans */
    @media (max-width: 400px) {
        .section-title { font-size: 1.4rem; }
    }

    /* Boutons pleine largeur sur mobile, largeur auto dès sm */
    @media (min-width: 576px) {
        .btn.w-sm-auto { width: auto !important; }
    }
</style>
@endpush

@endsection
