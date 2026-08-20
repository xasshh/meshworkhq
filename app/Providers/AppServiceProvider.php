<?php

namespace App\Providers;

use App\Events\BriefPublished;
use App\Events\BriefUnlocked;
use App\Listeners\MatchBriefToAlerts;
use App\Listeners\NotifyClientOfUnlock;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaystackGateway;
use App\Services\Verification\ManualNinVerifier;
use App\Services\Verification\NinVerifier;
use App\Support\Seo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Swap this binding for a licensed provider's implementation once API
        // credentials exist. Nothing outside this line needs to change.
        $this->app->scoped(Seo::class);

        $this->app->bind(NinVerifier::class, ManualNinVerifier::class);

        $this->app->singleton(PaymentGateway::class, fn (): PaystackGateway => new PaystackGateway(
            secretKey: config('services.paystack.secret'),
            callbackUrl: route('payment.callback'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // No forced https scheme here on purpose. `trustProxies(at: '*')` in
        // bootstrap/app.php already makes Laravel honour the X-Forwarded-Proto
        // header, so an ngrok tunnel generates https URLs on its own. Forcing
        // it would only break `php artisan serve`, which speaks http.
        $this->configureDefaults();
        $this->registerEventListeners();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerEventListeners(): void
    {
        Event::listen(BriefPublished::class, MatchBriefToAlerts::class);
        Event::listen(BriefUnlocked::class, NotifyClientOfUnlock::class);
    }
}
