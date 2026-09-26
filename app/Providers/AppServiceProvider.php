<?php

namespace App\Providers;

use App\Events\TokenBooked;
use App\Listeners\SendBookingNotification;
use App\Support\Navigation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.panel', function ($view): void {
            $view->with('navItems', auth()->check() ? Navigation::items(auth()->user()) : []);
        });

        Event::listen(TokenBooked::class, SendBookingNotification::class);
    }
}
