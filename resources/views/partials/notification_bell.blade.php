{{--
    Partial : Cloche de notifications
    À inclure dans layouts/app.blade.php et layouts/dashboard.blade.php

    DANS layouts/app.blade.php — dans la navbar, avant les boutons connexion :
    @auth
        @include('partials.notification_bell')
    @endauth

    DANS layouts/dashboard.blade.php — dans la topbar, avant @yield('topbar-actions') :
    @include('partials.notification_bell')
--}}

@auth
<div class="position-relative d-inline-block me-2" id="notif-wrapper">

    {{-- Bouton cloche --}}
    <button id="notif-bell"
            class="btn p-2 position-relative"
            style="background:none;border:none;color:#5C3D1E;line-height:1"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false"
            title="Notifications">
        <i class="bi bi-bell" style="font-size:1.3rem"></i>

        {{-- Badge rouge --}}
        <span id="notif-badge"
              style="display:none;position:absolute;top:2px;right:2px;
                     background:#C4622D;color:#fff;border-radius:100px;
                     min-width:18px;height:18px;font-size:.65rem;font-weight:700;
                     align-items:center;justify-content:center;
                     border:2px solid #fff;padding:0 3px;line-height:1">
        </span>
    </button>

    {{-- Dropdown notifications --}}
    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg"
         style="width:340px;max-height:480px;overflow:hidden;
                border:1px solid #ECD8C6;border-radius:14px">

        {{-- En-tête --}}
        <div class="d-flex justify-content-between align-items-center px-3 py-2"
             style="border-bottom:1px solid #ECD8C6;background:#F5EFE6">
            <span class="fw-700" style="font-size:.9rem;color:#2C1A0E">
                <i class="bi bi-bell me-2 text-clay"></i>Notifications
            </span>
            <a href="#" id="notif-mark-all"
               style="font-size:.78rem;color:#C4622D;font-weight:600;text-decoration:none">
                Tout marquer lu
            </a>
        </div>

        {{-- Liste (injectée par JS) --}}
        <div id="notif-list"
             style="overflow-y:auto;max-height:380px">
            <div class="text-center py-4 text-muted" style="font-size:.85rem">
                <span class="spinner-border spinner-border-sm me-2"
                      style="color:#C4622D"></span>
                Chargement...
            </div>
        </div>

        {{-- Pied --}}
        <div style="border-top:1px solid #ECD8C6;background:#F5EFE6">
            @if(auth()->user()->isArtisan())
                <a href="{{ route('artisan.orders.index') }}"
                   class="d-block text-center py-2"
                   style="font-size:.82rem;color:#C4622D;font-weight:600;text-decoration:none">
                    Voir toutes mes commandes →
                </a>
            @elseif(auth()->user()->isClient())
                <a href="{{ route('client.orders.index') }}"
                   class="d-block text-center py-2"
                   style="font-size:.82rem;color:#C4622D;font-weight:600;text-decoration:none">
                    Voir toutes mes commandes →
                </a>
            @elseif(auth()->user()->isLivreur())
                <a href="{{ route('livreur.dashboard') }}"
                   class="d-block text-center py-2"
                   style="font-size:.82rem;color:#C4622D;font-weight:600;text-decoration:none">
                    Voir mes missions →
                </a>
            @elseif(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}"
                   class="d-block text-center py-2"
                   style="font-size:.82rem;color:#C4622D;font-weight:600;text-decoration:none">
                    Voir le tableau de bord →
                </a>
            @endif
        </div>
    </div>
</div>
@endauth
