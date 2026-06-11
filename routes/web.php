<?php

use App\Enums\Role;
use App\Http\Controllers\UnlockController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featuredProfessionals = User::where('role', Role::Professional)
        ->whereNotNull('bio')
        ->whereNotNull('professional_title')
        ->latest()
        ->take(8)
        ->get();

    return view('welcome', compact('featuredProfessionals'));
})->name('home');

// Separated dual-entry auth paths
Route::middleware('guest')->group(function () {
    Route::view('professional/register', 'pages::auth.register-professional')->name('professional.register');
    Route::view('professional/login', 'pages::auth.login-professional')->name('professional.login');
    Route::view('client/register', 'pages::auth.register-client')->name('client.register');
    Route::view('client/login', 'pages::auth.login-client')->name('client.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return auth()->user()->isProfessional()
            ? redirect()->route('professional.dashboard')
            : redirect()->route('client.dashboard');
    })->name('dashboard');

    // Professional routes
    Route::middleware('role:professional')->prefix('professional')->name('professional.')->group(function () {
        Route::livewire('dashboard', 'pages::professional.dashboard')->name('dashboard');
        Route::livewire('alerts', 'pages::professional.alert-feed')->name('alerts');
        Route::livewire('wallet', 'pages::professional.wallet')->name('wallet');
        Route::livewire('brief/{ulid}', 'pages::professional.brief-detail')->name('brief.detail');

        // Unlock a brief (POST)
        Route::post('brief/{ulid}/unlock', [UnlockController::class, 'store'])
            ->name('brief.unlock');
    });

    // Client routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::livewire('dashboard', 'pages::client.dashboard')->name('dashboard');
        Route::livewire('brief/create', 'pages::client.brief-wizard')->name('brief.create');
        Route::livewire('briefs', 'pages::client.my-briefs')->name('briefs');
        Route::livewire('brief/{ulid}', 'pages::client.brief-detail')->name('brief.detail');
        Route::livewire('conversation/{id}', 'pages::shared.conversation')->name('conversation');
    });

    // Shared conversation (professional side uses same component, different guard)
    Route::middleware('role:professional')->prefix('professional')->name('professional.')->group(function () {
        Route::livewire('conversation/{id}', 'pages::shared.conversation')->name('conversation');
    });
});

require __DIR__.'/settings.php';
