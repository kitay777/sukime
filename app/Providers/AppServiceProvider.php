<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
    public function boot(UrlGenerator $url): void
    {
        Inertia::share([
            'auth' => fn () => ['user' => Auth::user()],
            'notifications' => fn () => Auth::check() 
                ? Auth::user()->unreadNotifications 
                : [],
        ]);
        $url->forceScheme('https');
        Vite::prefetch(concurrency: 3);
    }
}
