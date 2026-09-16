<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>403 — Accès refusé | ArtisanHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --clay:#C4622D; --earth:#2C1A0E; --sand:#F5EFE6; }
        * { font-family:'DM Sans',sans-serif; }
        h1,h2 { font-family:'Playfair Display',serif; }
        body { background:var(--sand); color:var(--earth); min-height:100vh;
               display:flex; flex-direction:column; }
        .error-code { font-size:clamp(5rem,15vw,10rem); font-weight:800;
                      color:var(--clay); opacity:.15; line-height:1;
                      font-family:'Playfair Display',serif; }
        .btn-clay { background:var(--clay); color:#fff; border:none; font-weight:700; }
        .btn-clay:hover { background:#9E4A1E; color:#fff; }
        .btn-outline-clay { border:2px solid var(--clay); color:var(--clay); font-weight:700; }
        .btn-outline-clay:hover { background:var(--clay); color:#fff; }
        nav { background:rgba(253,250,247,.97); border-bottom:1px solid #ECD8C6; }
        .brand { font-family:'Playfair Display',serif; font-weight:700;
                 color:var(--clay); font-size:1.3rem; text-decoration:none; }
    </style>
</head>
<body>
    <nav class="navbar px-4 py-3">
        <a href="/" class="brand">🏺 ArtisanHub</a>
    </nav>

    <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="text-center px-4" style="max-width:520px">
            <div class="error-code">403</div>
            <div style="font-size:2.5rem;margin:-20px 0 16px">🔒</div>
            <h1 style="font-size:1.8rem;margin-bottom:12px">Accès refusé</h1>
            <p class="text-muted mb-4" style="font-size:1rem;line-height:1.7">
                Vous n'avez pas les droits nécessaires pour accéder à cette page.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="/" class="btn btn-clay px-4">
                    <i class="bi bi-house me-2"></i>Accueil
                </a>
                <button onclick="history.back()" class="btn btn-outline-clay px-4">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </button>
            </div>
            @auth
                <div class="mt-4">
                    @if(auth()->user()->isArtisan())
                        <a href="{{ route('artisan.dashboard') }}" class="text-muted" style="font-size:.875rem">
                            Aller à mon tableau de bord →
                        </a>
                    @elseif(auth()->user()->isClient())
                        <a href="{{ route('client.dashboard') }}" class="text-muted" style="font-size:.875rem">
                            Aller à mon tableau de bord →
                        </a>
                    @endif
                </div>
            @endauth
        </div>
    </div>

    <footer class="text-center py-3" style="color:#9A8070;font-size:.85rem">
        © 2026 ArtisanHub · Bénin 🇧🇯
    </footer>
</body>
</html>
