<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\User;
use App\Observers\MemberObserver;
use Illuminate\Support\Facades\Gate;
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
    }
}
