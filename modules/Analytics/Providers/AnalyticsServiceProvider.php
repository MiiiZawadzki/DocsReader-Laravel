<?php

namespace Modules\Analytics\Providers;

use Illuminate\Support\ServiceProvider;
use Route;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'analytics');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');
    }
}
