@extends('layouts.app')
@section('title', 'Artisans qualifiés au Bénin')
@section('meta_description', "Trouvez un artisan qualifié près de chez vous au Bénin : poterie, menuiserie, couture, bijouterie. Devis gratuit, prix négocié, garantie satisfait ou repris sur ArtisanHub.")

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Organization",
    "name": "ArtisanHub",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('images/hero-artisans.png') }}",
    "description": "Plateforme de mise en relation entre particuliers et artisans qualifiés au Bénin.",
    "areaServed": {
        "@type": "Country",
        "name": "Bénin"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "WebSite",
    "name": "ArtisanHub",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ route('artisans.index') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endpush

@push('styles')
<style>
    .hero-photo {
        position: relative;
        overflow: hidden;
        padding: 110px 0 88px;
        isolation: isolate;
    }
    .hero-photo-img {
        position: absolute;
        inset: 0;
        z-index: -2;
        width: 100%;
        height: 100%;
        object-fit: cover;
        animation: ah-kenburns 22s ease-in-out infinite alternate;
        will-change: transform;
    }
    @keyframes ah-kenburns {
        0%   { transform: scale(1) translate(0, 0); }
        100% { transform: scale(1.12) translate(-1.5%, 1%); }
    }

    /* Voile dégradé aux couleurs du site pour la lisibilité du texte */
    .hero-overlay {
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(135deg, rgba(44,26,14,.82) 0%, rgba(92,61,30,.72) 55%, rgba(158,74,30,.62) 100%);
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-photo-img { animation: none !important; transform: none !important; }
    }
</style>
@endpush

@section('content')
<div class="hero-photo">
    <img class="hero-photo-img" src="{{ asset('images/hero-artisans.png') }}" alt="Artisans béninois au travail — poterie, tissage, menuiserie">
    <div class="hero-overlay"></div>

    <div class="container text-center">
        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.2rem);color:#fff;font-weight:800;line-height:1.15;max-width:680px;margin:0 auto 20px">
            L'artisanat béninois,<br><span style="color:#E8845A">au bout des doigts.</span>
        </h1>
        <p style="color:#C8B5A0;font-size:1rem;max-width:480px;margin:0 auto 36px">
            Connectez-vous aux meilleurs artisans du Bénin. Commandez sur mesure, directement à la source.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('artisans.index') }}" class="btn btn-clay px-4 py-2">
                <i class="bi bi-search me-2"></i>Trouver un artisan
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-light px-4 py-2">
                <i class="bi bi-person-plus me-2"></i>S'inscrire gratuitement
            </a>
        </div>
    </div>
</div>



<div class="py-5" style="background:#F5EFE6">
    <div class="container">
        <p style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="text-center mb-1">EXPLORER</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;text-align:center" class="mb-4">Toutes les spécialités</h2>
        <div class="row g-3">
            @foreach(config('artisanhub.categories') as $key => $cat)
                <div class="col-6 col-md-3">
                    <a href="{{ route('artisans.index', ['category'=>$key]) }}"
                       class="card p-3 text-center text-decoration-none h-100">
                        <div style="font-size:2rem">{{ $cat['icon'] }}</div>
                        <div class="fw-700 mt-1" style="color:#C4622D">{{ $cat['label'] }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="py-5 text-center">
    <div class="container">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem" class="mb-3">Prêt à démarrer ?</h2>
        <p class="text-muted mb-4">Inscription gratuite. Aucune commission sur les 3 premières commandes.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('register') }}" class="btn btn-clay px-4 py-2">
                <i class="bi bi-tools me-2"></i>Je suis artisan
            </a>
            <a href="{{ route('artisans.index') }}" class="btn btn-outline-clay px-4 py-2">
                <i class="bi bi-search me-2"></i>Je cherche un artisan
            </a>
        </div>
    </div>
</div>



{{-- ── CONFIANCE : paliers + fonds de garantie (notre vrai différenciateur) ── --}}
<div class="py-5" style="background:#2C1A0E">
    <div class="container">
        <p style="color:#E8845A;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="text-center mb-1">POURQUOI ARTISANHUB</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:#fff;text-align:center" class="mb-4">
            Une confiance qui se mesure, pas qui se déclare
        </h2>

        <div class="row g-3 text-center mb-4">
            <div class="col-6 col-md-3">
                <div class="fw-700" style="font-size:1.8rem;color:#E8845A">{{ $trust['artisans_actifs'] }}</div>
                <div style="font-size:.78rem;color:#C8B5A0">Artisans actifs</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-700" style="font-size:1.8rem;color:#E8845A">{{ $trust['artisans_experts'] }}</div>
                <div style="font-size:.78rem;color:#C8B5A0">Palier Expert 🏅</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-700" style="font-size:1.8rem;color:#E8845A">{{ $trust['artisans_verifies'] }}</div>
                <div style="font-size:.78rem;color:#C8B5A0">Identités vérifiées</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-700" style="font-size:1.8rem;color:#E8845A">{{ $trust['garanties_traitees'] }}</div>
                <div style="font-size:.78rem;color:#C8B5A0">Réclamations traitées</div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 rounded-3 h-100" style="background:rgba(255,255,255,.06)">
      Tables              <div style="font-size:1.5rem">🥉🥈🥇</div>
                    <h6 class="fw-700 mt-2 mb-1" style="color:#fff">Paliers de confiance</h6>
                    <p class="mb-0" style="font-size:.82rem;color:#C8B5A0">
                        Débutant → Confirmé → Expert, calculé à partir des commandes terminées,
                        de la note moyenne et du taux de litige — pas d'un simple badge acheté.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 h-100" style="background:rgba(255,255,255,.06)">
                    <div style="font-size:1.5rem">🛡️</div>
                    <h6 class="fw-700 mt-2 mb-1" style="color:#fff">Garantie satisfait ou repris</h6>
                    <p class="mb-0" style="font-size:.82rem;color:#C8B5A0">
                        Les artisans Expert vérifiés (pièce d'identité + téléphone confirmés)
                        cotisent à un fonds de garantie qui vous couvre en cas de litige.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 h-100" style="background:rgba(255,255,255,.06)">
                    <div style="font-size:1.5rem">💬</div>
                    <h6 class="fw-700 mt-2 mb-1" style="color:#fff">Prix négocié, pas imposé</h6>
                    <p class="mb-0" style="font-size:.82rem;color:#C8B5A0">
                        Faites une offre, l'artisan peut contre-proposer — comme dans la vraie vie,
                        avant tout engagement de paiement.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
