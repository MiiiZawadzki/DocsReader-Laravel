<?php

namespace Modules\Analytics\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Analytics\Strategies\Contracts\CompletionRateStrategyInterface;
use Modules\Analytics\Strategies\Contracts\SkimDetectionStrategyInterface;
use Modules\Analytics\Strategies\ProportionalCompletionRate;
use Modules\Analytics\Strategies\TotalThresholdSkimDetection;
use Route;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompletionRateStrategyInterface::class, ProportionalCompletionRate::class);
        $this->app->bind(SkimDetectionStrategyInterface::class, TotalThresholdSkimDetection::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'analytics');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');
    }
}
