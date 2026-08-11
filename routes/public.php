<?php

use App\Livewire\Public\Participants\Portal\Events as PublicParticipantsPortalEvents;
use App\Livewire\Public\Participants\Portal\Home as PublicParticipantsPortalHome;
use App\Livewire\Public\Participants\Register as PublicParticipantsRegister;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no auth)
|--------------------------------------------------------------------------
|
| Prefijo /p — ver .cursor/rules/public-routes.mdc
|
*/

Route::prefix('p')->name('public.')->group(function () {
    Route::get('/participants/register/{businessToken}', PublicParticipantsRegister::class)
        ->where('businessToken', '[A-Za-z0-9\-_]+')
        ->name('participants.register');

    Route::get('/participants/{businessToken}', PublicParticipantsPortalHome::class)
        ->where('businessToken', '[A-Za-z0-9\-_]+')
        ->name('participants.home');

    Route::get('/participants/{businessToken}/events', PublicParticipantsPortalEvents::class)
        ->where('businessToken', '[A-Za-z0-9\-_]+')
        ->name('participants.events');
});
