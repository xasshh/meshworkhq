<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\UnlockController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::welcome')->name('home');

Route::get('payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('webhooks/paystack', [PaymentController::class, 'webhook'])->name('webhooks.paystack');

Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::livewire('how-it-works', 'pages::how-it-works')->name('how-it-works');
Route::livewire('for-talent', 'pages::for-talent')->name('for-talent');

Route::livewire('professionals', 'pages::directory')->name('directory');
Route::livewire('professionals/{id}', 'pages::professional-profile')->name('professionals.show');

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
        Route::livewire('pitches', 'pages::professional.pitches')->name('pitches');
        Route::livewire('messages', 'pages::shared.messages')->name('messages');
        Route::livewire('profile', 'pages::professional.profile')->name('profile');
        Route::livewire('wallet', 'pages::professional.wallet')->name('wallet');
        Route::post('wallet/buy/{bundle}', [PaymentController::class, 'checkout'])->name('wallet.checkout');
        Route::livewire('brief/{ulid}', 'pages::professional.brief-detail')->name('brief.detail');
        Route::livewire('conversation/{id}', 'pages::shared.conversation')->name('conversation');

        // Unlock a brief (POST)
        Route::post('brief/{ulid}/unlock', [UnlockController::class, 'store'])
            ->name('brief.unlock');
    });

    // Client routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::livewire('dashboard', 'pages::client.dashboard')->name('dashboard');
        Route::livewire('brief/create', 'pages::client.brief-wizard')->name('brief.create');
        Route::livewire('briefs', 'pages::client.my-briefs')->name('briefs');
        Route::livewire('messages', 'pages::shared.messages')->name('messages');
        Route::livewire('profile', 'pages::client.profile')->name('profile');
        Route::livewire('verification', 'pages::client.verification')->name('verification');
        Route::livewire('brief/{ulid}', 'pages::client.brief-detail')->name('brief.detail');
        Route::livewire('conversation/{id}', 'pages::shared.conversation')->name('conversation');
    });
});

require __DIR__.'/settings.php';
