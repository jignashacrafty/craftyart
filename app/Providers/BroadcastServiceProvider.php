<?php

namespace App\Providers;

use App\Http\Middleware\BroadcastAuthenticate;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        Log::info("Broadcast ROUTES loaded");

        require base_path('routes/channels.php');
    }
}
