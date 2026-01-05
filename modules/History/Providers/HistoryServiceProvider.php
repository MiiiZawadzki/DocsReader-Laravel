<?php

namespace Modules\History\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\History\Api\HistoryApi;
use Modules\History\Api\HistoryApiInterface;
use Modules\History\Repositories\Contracts\DocumentReadRepositoryInterface;
use Modules\History\Repositories\DocumentReadRepository;
use Route;

class HistoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HistoryApiInterface::class, HistoryApi::class);
        $this->app->bind(DocumentReadRepositoryInterface::class, DocumentReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'history');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
