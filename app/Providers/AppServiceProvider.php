<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Observers\MemberObserver;
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
