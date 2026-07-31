<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Observers\MemberObserver;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        Gate::before(function ($user, string $ability) {
            return $user instanceof User && $user->hasRole('Super Admin') ? true : null;
        });

        Member::observe(MemberObserver::class);

        Notification::extend('sms', fn ($app) => new SmsChannel);

        // Laravel's paginator defaults to Tailwind-styled links, but this app is
        // Bootstrap 5 throughout — without this, every ->links() call renders
        // unstyled markup.
        Paginator::useBootstrapFive();

        // Neither the staff admin dashboard (route name "admin.dashboard") nor the
        // member portal dashboard ("member.dashboard") is literally named "dashboard",
        // so Laravel's default guest-middleware redirect falls through to the "home"
        // route (the public marketing site) whenever an already-authenticated user
        // hits a login page. Route by request path instead.
        RedirectIfAuthenticated::redirectUsing(fn ($request) => $request->is('member/login')
            ? route('member.dashboard')
            : route('admin.dashboard'));

        $this->applySettingsOverrides();
    }

    /**
     * Apply Admin > Settings values on top of the .env-based defaults, so the
     * Settings module's Email fields actually take effect without editing
     * server files. Guarded for early-boot states (e.g. before migrations
     * have run) where the settings table doesn't exist yet.
     */
    private function applySettingsOverrides(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        if ($host = setting('mail_host')) {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => setting('mail_port', 587),
                'mail.mailers.smtp.username' => setting('mail_username'),
                'mail.mailers.smtp.password' => setting('mail_password'),
            ]);
        }

        if ($fromAddress = setting('mail_from_address')) {
            config(['mail.from.address' => $fromAddress]);
        }

        if ($fromName = setting('mail_from_name')) {
            config(['mail.from.name' => $fromName]);
        }
    }
}
