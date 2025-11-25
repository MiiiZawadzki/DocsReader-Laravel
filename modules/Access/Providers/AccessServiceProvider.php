<?php

namespace Modules\Access\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Access\Api\AccessApi;
use Modules\Access\Api\AccessApiInterface;
use Modules\Access\Console\Commands\SyncPermissions;
use Modules\Access\Repository\AccessRepository;
use Modules\Access\Repository\Contracts\AccessRepositoryInterface;

class AccessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccessApiInterface::class, AccessApi::class);
        $this->app->bind(AccessRepositoryInterface::class, AccessRepository::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/permissions.php', 'permissions'
        );
    }
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPermissions::class,
            ]);
        }
    }
}
