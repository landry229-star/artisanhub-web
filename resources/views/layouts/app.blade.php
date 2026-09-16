<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#C4622D">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    {{-- ── SEO : titre, description, canonical ────────────────────────────── --}}
    <title>@yield('title', 'ArtisanHub') — Plateforme Artisans Bénin</title>
    <meta name="description" content="@yield('meta_description', "ArtisanHub connecte particuliers et artisans qualifiés partout au Bénin : poterie, menuiserie, couture, bijouterie et plus. Devis gratuit, paiement sécurisé, garantie satisfait ou repris.")">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="robots" content="@yield('robots', 'index, follow')">

    {{-- ── Open Graph (aperçus WhatsApp / Facebook) ────────────────────────── --}}
    <meta property="og:site_name" content="ArtisanHub">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', $__env->yieldContent('title', 'ArtisanHub'))">
    <meta property="og:description" content="@yield('meta_description', "ArtisanHub connecte particuliers et artisans qualifiés partout au Bénin : poterie, menuiserie, couture, bijouterie et plus.")">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/hero-artisans.png'))">
    <meta property="og:locale" content="fr_BJ">

    {{-- ── Twitter Card ─────────────────────────────────────────────────────── --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', $__env->yieldContent('title', 'ArtisanHub'))">
    <meta name="twitter:description" content="@yield('meta_description', "ArtisanHub connecte particuliers et artisans qualifiés partout au Bénin.")">
    <meta name="twitter:image" content="@yield('og_image', asset('images/hero-artisans.png'))">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600&family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @stack('head')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('sw.js') }}'));
        }
    </script>
    <style>
        :root {
            --clay:       #C4622D;
            --clay-dark:  #9E4A1E;
            --clay-light: #E8845A;
            --earth:      #2C1A0E;
            --earth-mid:  #5C3D1E;
            --gold:       #D4A853;
            --sand:       #F5EFE6;
            --sand2:      #FAF5EE;
            --white:      #FDFAF7;
        }
        * { font-family: 'DM Sans', sans-serif; }
        h1,h2,h3,.brand { font-family: 'Playfair Display', serif; }
        body { background: var(--white); color: var(--earth); }
        ::selection { background:var(--clay-light); color:#fff; }
        a, button, input, select, textarea { transition: color .2s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease; }
        :focus-visible { outline:3px solid rgba(212,168,83,.85); outline-offset:3px; }
        .navbar-toggler { border-color:var(--clay); }
        .navbar-toggler:focus { box-shadow:0 0 0 .2rem rgba(196,98,45,.2); }
        .btn { border-radius:10px; }
        .alert { border-radius:12px; }
        .dropdown-menu { border:1px solid #ECD8C6; border-radius:12px; box-shadow:0 12px 32px rgba(44,26,14,.12); }
        @media (max-width:575.98px) {
            .navbar-brand { font-size:1.15rem; }
            .navbar .btn { padding-inline:.65rem; }
            .hero-photo { padding-top:78px !important; padding-bottom:68px !important; }
        }

        /* Navbar */
        .navbar-artisan { background: rgba(253,250,247,.97); backdrop-filter: blur(8px);
            border-bottom: 1px solid #ECD8C6; }
        .navbar-brand { font-family:'Playfair Display',serif; font-weight:700;
            font-size:1.4rem; color:var(--clay)!important; }
        .nav-link { color:var(--earth-mid)!important; font-weight:500; }
        .nav-link:hover { color:var(--clay)!important; }

        /* Boutons */
        .btn-clay { background:var(--clay); color:#fff; border:none; font-weight:700; }
        .btn-clay:hover { background:var(--clay-dark); color:#fff; }
        .btn-outline-clay { border:2px solid var(--clay); color:var(--clay); background:transparent; font-weight:700; }
        .btn-outline-clay:hover { background:var(--clay); color:#fff; }

        /* Cards */
        .card { border:1px solid #ECD8C6; border-radius:14px; }
        .card:hover { box-shadow:0 6px 24px rgba(196,98,45,.12); transition:.2s; }

        /* Badges */
        .badge-top    { background:var(--clay);  color:#fff; }
        .badge-verif  { background:#2E7D32;      color:#fff; }
        .badge-new    { background:var(--gold);  color:#fff; }
        .badge-avail  { background:#1B5E20;      color:#fff; }
        .badge-busy   { background:#B71C1C;      color:#fff; }

        /* Stars */
        .stars { color:var(--gold); }

        /* Alerts */
        .alert-clay { background:#FFF0E8; border-color:#FFCBA4; color:#8B3A1A; }

        /* Section titles */
        .section-label { color:var(--clay); font-size:.75rem; font-weight:700;
            letter-spacing:.12em; text-transform:uppercase; }
        .section-title { font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:700; }

        /* Footer */
        footer { background:var(--earth); color:#9A8070; font-size:.875rem; }
        footer a { color:#9A8070; text-decoration:none; }
        footer a:hover { color:var(--clay-light); }

        /* Misc */
        .divider { border-color:#ECD8C6; }
        .text-clay { color:var(--clay)!important; }
        .bg-sand   { background:var(--sand)!important; }
        .bg-earth  { background:var(--earth)!important; }
        hr { border-color:#ECD8C6; }

         /* ── Notifications ────────────────────────────────────────────────────── */
.notif-item {
    transition: background .15s;
    cursor: pointer;
}
.notif-item:hover {
    background: #FAF5EE !important;
}
.notif-unread {
    background: #FFF8F5;
}
.notif-read {
    background: #fff;
}
@keyframes ah-pulse {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.25); }
    100% { transform: scale(1); }
}
.ah-pulse {
    animation: ah-pulse .4s ease;
}

/* Cloche qui se secoue quand il y a des notifs non lues */
@keyframes bell-shake {
    0%,100% { transform: rotate(0deg); }
    20%      { transform: rotate(-15deg); }
    40%      { transform: rotate(15deg); }
    60%      { transform: rotate(-10deg); }
    80%      { transform: rotate(10deg); }
}
#notif-bell:has(+ .ah-pulse),
#notif-bell.has-unread i {
    animation: bell-shake .6s ease;
}
    </style>
    @stack('styles')
</head>
<body>
<a class="visually-hidden-focusable position-fixed top-0 start-0 p-3 bg-white text-clay" href="#main-content">Aller au contenu</a>

{{-- NAVBAR --}}
<nav class="navbar navbar-expand-lg navbar-artisan sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">🏺 ArtisanHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('artisans.index') }}">Explorer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('how_it_works') ? 'text-clay fw-700' : '' }}"
                       href="{{ route('how_it_works') }}">Comment ça marche</a>
                </li>
            </ul>
            <div class="d-flex gap-2 align-items-center">
    @guest
        <a href="{{ route('login') }}" class="btn btn-outline-clay btn-sm">Connexion</a>
        <a href="{{ route('register') }}" class="btn btn-clay btn-sm">S'inscrire</a>
    @endguest
    @auth
        {{-- Cloche notifications --}}
        @include('partials.notification_bell')

        {{-- Menu utilisateur --}}
        <div class="dropdown">
            <button type="button" class="btn btn-clay btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ auth()->user()->avatarUrl() }}" class="rounded-circle me-1"
                     width="22" height="22" style="object-fit:cover">
                {{ auth()->user()->name }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if(auth()->user()->isArtisan())
                    <li><a class="dropdown-item" href="{{ route('artisan.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>Mon espace</a></li>
                @elseif(auth()->user()->isClient())
                    <li><a class="dropdown-item" href="{{ route('client.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>Mon espace</a></li>
                @elseif(auth()->user()->isLivreur())
                    <li><a class="dropdown-item" href="{{ route('livreur.dashboard') }}">
                        <i class="bi bi-bicycle me-2"></i>Mes missions</a></li>
                @elseif(auth()->user()->isAdmin())
                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-shield-check me-2"></i>Admin</a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    @endauth
</div>
    </div>
    </div>
</nav>

{{-- FLASH MESSAGES --}}
@if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3 mb-0" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show m-3 mb-0" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- CONTENU --}}
<main id="main-content">
    @yield('content')
</main>

{{-- FOOTER --}}
<footer class="py-4 mt-5" style="background:#2C1A0E;color:#9A8070">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-md-4">
                <span style="font-family:'Playfair Display',serif;font-size:1.1rem;color:#fff">
                    🏺 ArtisanHub
                </span>
                <p class="mt-2 mb-0" style="font-size:.82rem">
                    Plateforme de mise en relation entre artisans et clients au Bénin 🇧🇯
                </p>
            </div>
            <div class="col-md-4 text-center">
                <p class="mb-1" style="font-size:.82rem">
                    © 2026 ArtisanHub · Cotonou, Bénin
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="{{ route('cgu') }}"
                       style="color:#9A8070;font-size:.8rem;text-decoration:none">CGU</a>
                    <a href="{{ route('privacy') }}"
                       style="color:#9A8070;font-size:.8rem;text-decoration:none">Confidentialité</a>
                    <a href="{{ route('mentions') }}"
                       style="color:#9A8070;font-size:.8rem;text-decoration:none">Mentions légales</a>
                    <a href="{{ route('remboursement') }}"
                       style="color:#9A8070;font-size:.8rem;text-decoration:none">Remboursement</a>
                    <a href="{{ route('support') }}"
                       style="color:#9A8070;font-size:.8rem;text-decoration:none">Support</a>
                    <a href="mailto:contact@artisanhub.bj"
                       style="color:#9A8070;font-size:.8rem;text-decoration:none">Contact</a>
                </div>
            </div>
            <div class="col-md-4 text-end">
                @auth
                    @if(auth()->user()->isArtisan() && !auth()->user()->email_verified_at)
                        <a href="{{ route('email.notice') }}"
                           class="btn btn-sm btn-warning" style="font-size:.78rem">
                            ⚠️ Vérifiez votre email
                        </a>
                    @endif
                @endauth
                <p class="mt-2 mb-0" style="font-size:.78rem">
                    <a href="https://wa.me/22997000000" target="_blank"
                       style="color:#9A8070;text-decoration:none">
                        <i class="bi bi-whatsapp me-1"></i>Support WhatsApp
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>

@include('partials.scripts')
</body>
</html>
