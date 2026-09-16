<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use App\View\Composers\AdminSidebarComposer;
use App\View\Composers\ArtisanSidebarComposer;
use App\Notifications\Channels\WhatsAppChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Le composer se déclenche uniquement quand ce partial est rendu,
        // donc aucun coût sur les pages non-admin.
        View::composer('admin.partials.sidebar', AdminSidebarComposer::class);
        View::composer('artisan.partials.sidebar', ArtisanSidebarComposer::class);

        // Canal custom : notifications critiques relayées par WhatsApp/SMS
        // pour les utilisateurs peu à l'aise avec le tableau de bord web.
        Notification::extend('whatsapp', fn($app) => $app->make(WhatsAppChannel::class));
    }
}
