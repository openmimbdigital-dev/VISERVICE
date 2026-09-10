<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:expire')->daily();

// El proveedor DIAN no notifica por webhook: el estado se consulta periódicamente.
Schedule::command('dian:sync-status')->everyTenMinutes()->withoutOverlapping();

// Bold sí notifica, pero un aviso perdido deja al comercio pagando sin activarse:
// los cobros con link pendiente se contrastan contra la pasarela.
Schedule::command('bold:sync-payments')->everyFiveMinutes()->withoutOverlapping();
