<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Roles\Index as AdminRolesIndex;
use App\Livewire\Admin\User\Index as AdminUserIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/admin/users', AdminUserIndex::class)->name('admin.users.index');

    Route::get('/admin/roles', AdminRolesIndex::class)->name('admin.roles.index');
});
