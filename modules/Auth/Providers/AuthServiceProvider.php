<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Route;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
//        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');
        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'auth');
    }
}
