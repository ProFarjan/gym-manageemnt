<?php

namespace App\Providers;

use App\Models\MailLog;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Observers\MemberObserver;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
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

        // Required by the default 'api' middleware group (routes/api.php) —
        // the ZKTeco Windows Service calls this on its own sync cycle
        // (default every 5 min), so this is generous headroom, not a real limit.
        RateLimiter::for('api', fn ($request) => Limit::perMinute(120)->by($request->ip()));

        // Logs every email the app actually sends (Settings > Email > Mail
        // Log table), regardless of which notification/mailable triggered
        // it — fires for any mailer (log/smtp/etc), not just SMTP.
        Event::listen(MessageSent::class, function (MessageSent $event) {
            try {
                $to = collect($event->message->getTo())
                    ->map(fn ($address) => $address->getAddress())
                    ->implode(', ');

                MailLog::create([
                    'to_address' => $to !== '' ? $to : 'unknown',
                    'subject' => $event->message->getSubject(),
                    'mailer' => config('mail.default'),
                    'status' => 'sent',
                ]);
            } catch (\Throwable) {
                // Never let logging break an email that already sent.
            }
        });
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
                // Configuring the smtp mailer's own params was never enough
                // on its own — mail.default stayed 'log' regardless, so
                // every notification silently went to the log file instead
                // of actually sending, however this was filled in.
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => setting('mail_port', 587),
                'mail.mailers.smtp.username' => setting('mail_username'),
                'mail.mailers.smtp.password' => setting('mail_password'),
                // Port 465 (Gmail's SSL port, among others) needs *implicit*
                // TLS requested explicitly via scheme=smtps — Symfony Mailer
                // doesn't infer this from the port number alone. Port 587
                // (STARTTLS) needs no scheme override; it negotiates
                // encryption automatically once connected.
                'mail.mailers.smtp.scheme' => setting('mail_encryption') === 'ssl' ? 'smtps' : null,
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
