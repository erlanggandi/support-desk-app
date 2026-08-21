<?php

namespace App\Providers;

use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureRateLimiting();

        // Audit otomatis login/logout (PRD: audit log mencakup auth).
        Event::listen(Login::class, fn (Login $e) => AuditLog::record('auth.login', 'user', $e->user?->id));
        Event::listen(Logout::class, fn (Logout $e) => AuditLog::record('auth.logout', 'user', $e->user?->id));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
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

    /**
     * Rate limit endpoint publik anti-spam / brute force tracking.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('tickets', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }
}
