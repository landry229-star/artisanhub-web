{{--
    Sidebar artisan unifiée.
    Avant : chaque page (dashboard, commandes, services, portfolio, profil,
    solde, stats) avait sa propre copie du menu, avec des liens manquants
    selon la page (ex : "Mes statistiques" n'apparaissait presque nulle
    part, "Mon solde" et "Mes services" étaient absents de la page
    Commandes) et le lien "actif" codé en dur au lieu d'être calculé —
    donc parfois toujours affiché actif même sur une autre page.
    On centralise ici pour que la navigation soit identique partout.
--}}
<div class="sidebar-section-title">Principal</div>

<a href="{{ route('artisan.dashboard') }}"
   class="sidebar-link {{ request()->routeIs('artisan.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Tableau de bord
</a>

<a href="{{ route('artisan.orders.index') }}"
   class="sidebar-link {{ request()->routeIs('artisan.orders.*') ? 'active' : '' }}">
    <i class="bi bi-bag-check"></i> Mes commandes
    @isset($stats['pending'])
        @if($stats['pending'] > 0)
            <span class="badge ms-auto" style="background:var(--clay)">{{ $stats['pending'] }}</span>
        @endif
    @endisset
</a>

<a href="{{ route('artisan.wallet.index') }}"
   class="sidebar-link {{ request()->routeIs('artisan.wallet.*') ? 'active' : '' }}">
    <i class="bi bi-wallet2"></i> Mon solde
</a>

<a href="{{ route('artisan.stats.index') }}"
   class="sidebar-link {{ request()->routeIs('artisan.stats.*') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> Mes statistiques
</a>

<a href="{{ route('artisan.reviews.index') }}"
   class="sidebar-link {{ request()->routeIs('artisan.reviews.*') ? 'active' : '' }}">
    <i class="bi bi-star-half"></i> Mes avis
    @if(($pendingReplies ?? 0) > 0)
        <span class="badge ms-auto" style="background:var(--clay)">{{ $pendingReplies }}</span>
    @endif
</a>

<a href="{{ route('contacts.index') }}"
   class="sidebar-link {{ request()->routeIs('contacts.index') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Mes contacts
</a>

<div class="sidebar-section-title mt-2">Mon profil</div>

<a href="{{ route('artisan.services.index') }}"
   class="sidebar-link {{ request()->routeIs('artisan.services.*') ? 'active' : '' }}">
    <i class="bi bi-grid-3x3-gap"></i> Mes services
</a>

<a href="{{ route('artisan.portfolio.index') }}"
   class="sidebar-link {{ request()->routeIs('artisan.portfolio.*') ? 'active' : '' }}">
    <i class="bi bi-images"></i> Mon portfolio
</a>

<a href="{{ route('artisan.profile.edit') }}"
   class="sidebar-link {{ request()->routeIs('artisan.profile.*') ? 'active' : '' }}">
    <i class="bi bi-person-gear"></i> Modifier mon profil
</a>
