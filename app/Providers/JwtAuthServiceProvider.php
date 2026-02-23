<?php

namespace App\Providers;

use App\Auth\JwtGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class JwtAuthServiceProvider extends ServiceProvider
{
  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();

    // Register JWT guard
    Auth::extend('jwt', function ($app, $name, array $config) {
      return new JwtGuard(
        Auth::createUserProvider($config['provider']),
        $app->make('request')
      );
    });
  }
}
