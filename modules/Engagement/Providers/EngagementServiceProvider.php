<?php

namespace Modules\Engagement\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Engagement\Api\EngagementApi;
use Modules\Engagement\Api\EngagementApiInterface;
use Modules\Engagement\Repositories\Contracts\PageProgressRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\PageTickRepositoryInterface;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;
use Modules\Engagement\Repositories\PageProgressRepository;
use Modules\Engagement\Repositories\PageTickRepository;
use Modules\Engagement\Repositories\ReadingSessionRepository;
use Modules\Engagement\Strategies\Contracts\EngagementRuleStrategyInterface;
use Modules\Engagement\Strategies\EveryPageMeetsThresholdRule;
use Route;

class EngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReadingSessionRepositoryInterface::class, ReadingSessionRepository::class);
        $this->app->bind(PageTickRepositoryInterface::class, PageTickRepository::class);
        $this->app->bind(PageProgressRepositoryInterface::class, PageProgressRepository::class);
        $this->app->bind(EngagementApiInterface::class, EngagementApi::class);

        $this->app->bind(EngagementRuleStrategyInterface::class, EveryPageMeetsThresholdRule::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'engagement');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');
    }
}
