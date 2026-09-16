{{--
    Sidebar admin unifiée.
    Avant : chaque page avait sa propre copie du menu, incomplète ou désynchronisée
    (ex : la page Utilisateurs et Commandes n'avaient pas les liens Messages / Reversements / Livraisons,
    et le tableau de bord avait "Messages" et "Livraisons" codés en dur en "active" même quand
    on n'était pas sur ces pages). On centralise ici pour que tout reste cohérent.
--}}
<div class="sidebar-section-title">Administration</div>

<a href="{{ route('admin.dashboard') }}"
   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-shield-check"></i> Tableau de bord
</a>

<a href="{{ route('admin.users.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Utilisateurs
    @isset($sidebarBadges['unverified'])
        @if($sidebarBadges['unverified'] > 0)
            <span class="badge ms-auto" style="background:#D4A853">{{ $sidebarBadges['unverified'] }}</span>
        @endif
    @endisset
</a>

<a href="{{ route('admin.orders.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
    <i class="bi bi-bag"></i> Commandes
    @isset($sidebarBadges['disputed_orders'])
        @if($sidebarBadges['disputed_orders'] > 0)
            <span class="badge ms-auto" style="background:#dc3545">{{ $sidebarBadges['disputed_orders'] }}</span>
        @endif
    @endisset
</a>

<a href="{{ route('admin.deliveries.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
    <i class="bi bi-bicycle"></i> Livraisons
</a>

<a href="{{ route('admin.messages.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
    <i class="bi bi-chat-square-text"></i> Messages
    @isset($sidebarBadges['flagged_msgs'])
        @if($sidebarBadges['flagged_msgs'] > 0)
            <span class="badge ms-auto" style="background:#dc3545">{{ $sidebarBadges['flagged_msgs'] }}</span>
        @endif
    @endisset
</a>

<a href="{{ route('admin.support.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
   <i class="bi bi-headset"></i> Support
</a>

<a href="{{ route('admin.wallet.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.wallet.*') ? 'active' : '' }}">
   <i class="bi bi-cash-coin"></i> Reversements
</a>

<a href="{{ route('admin.guarantee-claims.index') }}"
   class="sidebar-link {{ request()->routeIs('admin.guarantee-claims.*') ? 'active' : '' }}">
    <i class="bi bi-shield-check"></i> Garantie
    @isset($sidebarBadges['pending_guarantee_claims'])
        @if($sidebarBadges['pending_guarantee_claims'] > 0)
            <span class="badge ms-auto" style="background:#dc3545">{{ $sidebarBadges['pending_guarantee_claims'] }}</span>
        @endif
    @endisset
</a>

<div class="sidebar-section-title mt-2">Mon compte</div>
<a href="{{ route('admin.profile.edit') }}"
   class="sidebar-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
    <i class="bi bi-person-gear"></i> Mon profil
</a>

<div class="sidebar-section-title mt-2">Exports</div>
<a href="{{ route('admin.users.export', ['format' => 'excel']) }}" class="sidebar-link">
    <i class="bi bi-file-earmark-spreadsheet"></i> Utilisateurs Excel
</a>
<a href="{{ route('admin.users.export', ['format' => 'pdf']) }}" class="sidebar-link">
    <i class="bi bi-file-earmark-pdf"></i> Utilisateurs PDF
</a>
<a href="{{ route('admin.orders.export', ['format' => 'excel']) }}" class="sidebar-link">
    <i class="bi bi-file-earmark-spreadsheet"></i> Commandes Excel
</a>
<a href="{{ route('admin.orders.export', ['format' => 'pdf']) }}" class="sidebar-link">
    <i class="bi bi-file-earmark-pdf"></i> Commandes PDF
</a>
<a href="{{ route('admin.exports.index') }}" class="sidebar-link {{ request()->routeIs('admin.exports.*') ? 'active' : '' }}">
    <i class="bi bi-clock-history"></i> Historique exports
</a>
