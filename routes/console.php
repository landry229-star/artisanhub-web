<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nettoyage des devis restés sans réponse (voir Quote::EXPIRY_HOURS).
Schedule::command('quotes:expire')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('orders:alert-delayed')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
