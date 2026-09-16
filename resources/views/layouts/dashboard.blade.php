<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#C4622D">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <title>@yield('title', 'Dashboard') — ArtisanHub</title>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' }));
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;600;700&family=DM+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --clay:#C4622D; --clay-dark:#9E4A1E; --clay-light:#E8845A;
            --earth:#2C1A0E; --earth-mid:#5C3D1E;
            --gold:#D4A853; --sand:#F5EFE6; --white:#FDFAF7;
            --sidebar-w: 260px;
        }
        * { font-family:'DM Sans',sans-serif; }
        h1,h2,h3,.brand { font-family:'Playfair Display',serif; }
        body { background:#F0EBE3; color:var(--earth); min-height:100vh; }
        ::selection { background:var(--clay-light); color:#fff; }
        :focus-visible { outline:3px solid rgba(212,168,83,.85); outline-offset:3px; }
        .btn { border-radius:10px; }
        .sidebar-link, .stat-card, .content-card { transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease; }
        .stat-card:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(44,26,14,.08); }
        .content-card { box-shadow:0 2px 8px rgba(44,26,14,.03); }
        .topbar { box-shadow:0 2px 8px rgba(44,26,14,.04); }






        /* ── Sidebar ─────────────────────────────────────────────────── */
        .sidebar {
            position:fixed; top:0; left:0; bottom:0;
            width:var(--sidebar-w); background:var(--earth);
            display:flex; flex-direction:column;
            z-index:200; overflow-y:auto;
        }
        .sidebar-brand {
            padding:24px 20px 16px;
            font-family:'Playfair Display',serif;
            font-size:1.3rem; color:#fff; font-weight:700;
            border-bottom:1px solid rgba(255,255,255,.08);
        }
        .sidebar-user {
            padding:16px 20px;
            border-bottom:1px solid rgba(255,255,255,.08);
            display:flex; align-items:center; gap:12px;
        }
        .sidebar-user img {
            width:40px; height:40px; border-radius:50%;
            object-fit:cover; border:2px solid var(--clay);
        }
        .sidebar-user-name  { color:#fff; font-weight:600; font-size:.9rem; line-height:1.2; }
        .sidebar-user-role  { color:#9A8070; font-size:.75rem; }
        .sidebar-nav { padding:12px 0; flex:1; }
        .sidebar-section-title {
            color:#6B5545; font-size:.7rem; font-weight:700;
            letter-spacing:.12em; text-transform:uppercase;
            padding:12px 20px 4px;
        }
        .sidebar-link {
            display:flex; align-items:center; gap:10px;
            padding:10px 20px; color:#B8A090; text-decoration:none;
            font-weight:500; font-size:.9rem; transition:.15s;
            border-left:3px solid transparent;
        }
        .sidebar-link:hover { color:#fff; background:rgba(255,255,255,.05); }
        .sidebar-link.active { color:#fff; background:rgba(196,98,45,.15);
            border-left-color:var(--clay); }
        .sidebar-link i { font-size:1.1rem; width:20px; text-align:center; }
        .sidebar-footer {
            padding:16px 20px;
            border-top:1px solid rgba(255,255,255,.08);
        }
        .sidebar-logout {
            display:flex; align-items:center; gap:10px;
            color:#9A8070; font-size:.875rem; cursor:pointer;
            background:none; border:none; padding:0; width:100%;
            transition:.15s;
        }
        .sidebar-logout:hover { color:#FF6B6B; }

        /* ── Main ────────────────────────────────────────────────────── */
        .main-content {
            margin-left:var(--sidebar-w);
            min-height:100vh; padding:0;
        }
        .topbar {
            background:var(--white); border-bottom:1px solid #ECD8C6;
            padding:12px 28px; display:flex; align-items:center;
            justify-content:space-between; position:sticky; top:0; z-index:100;
        }
        .topbar-title { font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:700; }
        .page-body { padding:28px; }

        /* ── Cards ───────────────────────────────────────────────────── */
        .stat-card {
            background:var(--white); border:1px solid #ECD8C6;
            border-radius:14px; padding:20px 24px;
        }
        .stat-card .stat-icon {
            width:48px; height:48px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.3rem;
        }
        .stat-card .stat-value { font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:700; }
        .stat-card .stat-label { color:var(--earth-mid); font-size:.85rem; }

        .content-card {
            background:var(--white); border:1px solid #ECD8C6;
            border-radius:14px; padding:24px;
        }

        /* ── Table ───────────────────────────────────────────────────── */
        .table { --bs-table-bg:transparent; }
        .table th { color:var(--earth-mid); font-size:.8rem; text-transform:uppercase;
            letter-spacing:.08em; font-weight:700; border-bottom:2px solid #ECD8C6; }
        .table td { border-bottom:1px solid #F5EFE6; vertical-align:middle; }

        /* ── Badges statuts ──────────────────────────────────────────── */
        .badge-status { font-size:.75rem; padding:4px 10px; border-radius:100px; font-weight:600; }
        .status-en_attente  { background:#FFF3CD; color:#856404; }
        .status-acceptee    { background:#CFF4FC; color:#055160; }
        .status-en_cours    { background:#CCE5FF; color:#004085; }
        .status-livree      { background:#E2E3E5; color:#383D41; }
        .status-terminee    { background:#D4EDDA; color:#155724; }
        .status-annulee     { background:#F8D7DA; color:#721C24; }
        .status-litige      { background:#F8D7DA; color:#721C24; border:1px solid #F5C6CB; }

        /* ── Buttons ─────────────────────────────────────────────────── */
        .btn-clay { background:var(--clay); color:#fff; border:none; font-weight:700; }
        .btn-clay:hover { background:var(--clay-dark); color:#fff; }
        .btn-outline-clay { border:2px solid var(--clay); color:var(--clay); font-weight:700; }
        .btn-outline-clay:hover { background:var(--clay); color:#fff; }

        /* ── Responsive ──────────────────────────────────────────────── */
        /*
           IMPORTANT : le seuil est 767.98px (et pas 768px) pour rester
           exactement calé sur les breakpoints Bootstrap (.d-md-none se
           masque à partir de 768px). Avec "max-width:768px", il existait
           une largeur (exactement 768px) où le sidebar restait caché
           hors écran ET le bouton hamburger (d-md-none) était lui aussi
           caché : impossible de rouvrir le menu. C'était le vrai bug
           empêchant l'usage correct sur certains téléphones/tablettes.
        */
        @media(max-width:767.98px) {
            .sidebar { transform:translateX(-100%); transition:.3s; }
            .sidebar.open { transform:none; }
            .main-content { margin-left:0; }

            .topbar { padding:10px 16px; }
            .page-body { padding:16px; }
            .topbar-title { font-size:1.05rem; }

            .stat-card { padding:14px 16px; }
            .stat-card .stat-value { font-size:1.3rem; }
            .content-card { padding:16px; }
        }

        /* Empêche tout débordement horizontal accidentel sur mobile */
        html, body { overflow-x:hidden; max-width:100%; }

        /* Fond assombri derrière le menu quand il est ouvert sur mobile */
        .sidebar-backdrop {
            display:none;
            position:fixed; inset:0;
            background:rgba(0,0,0,.45);
            z-index:190;
        }
        .sidebar-backdrop.open { display:block; }

        .text-clay { color:var(--clay)!important; }
        .stars { color:var(--gold); }

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

{{-- Fond assombri mobile : cliquer dessus ferme le menu --}}
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">🏺 ArtisanHub</div>

    <div class="sidebar-user">
        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}">
        <div>
            <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-user-role">
                {{ ucfirst(auth()->user()->role) }}
                @if(auth()->user()->isArtisan() && auth()->user()->artisanProfile?->is_available)
                    <span class="ms-1" style="color:#4CAF50">●</span>
                @elseif(auth()->user()->isArtisan())
                    <span class="ms-1" style="color:#F44336">●</span>
                @endif
            </div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Navigation principale">
        @yield('sidebar-nav')
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="bi bi-box-arrow-right"></i>
                Déconnexion
            </button>
        </form>
    </div>
</aside>

{{-- MAIN --}}
<main class="main-content" id="main-content">

    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button
                class="btn btn-sm d-md-none"
                id="sidebarToggle"
                aria-label="Ouvrir le menu"
                aria-expanded="false"
                aria-controls="sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>

            <span class="topbar-title">
                @yield('page-title', 'Dashboard')
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            @include('partials.notification_bell')
            @yield('topbar-actions')
        </div>
    </div>

    <div class="page-body">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Erreurs de validation :</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</main>

@include('partials.scripts')

<script>
    // Menu mobile : ouverture/fermeture propre avec fond assombri,
    // fermeture au clic dehors, à la touche Échap, et blocage du
    // scroll de la page pendant que le menu est ouvert.
    (function () {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggle   = document.getElementById('sidebarToggle');
        if (!sidebar || !backdrop || !toggle) return;

        function openMenu() {
            sidebar.classList.add('open');
            backdrop.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            sidebar.classList.remove('open');
            backdrop.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
        toggle.addEventListener('click', function () {
            sidebar.classList.contains('open') ? closeMenu() : openMenu();
        });
        backdrop.addEventListener('click', closeMenu);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
        // Si l'écran repasse en desktop pendant que le menu mobile est ouvert
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) closeMenu();
        });
    })();
</script>

</body>
</html>
