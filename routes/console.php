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
