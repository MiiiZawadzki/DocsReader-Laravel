<?php

namespace Modules\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;
use Route;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
