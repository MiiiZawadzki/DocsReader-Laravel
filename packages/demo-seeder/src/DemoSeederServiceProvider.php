<?php

namespace DocsReader\DemoSeeder;

use DocsReader\DemoSeeder\Console\SeedDemoData;
use Illuminate\Support\ServiceProvider;

/**
 * Discovered through extra.laravel.providers, so removing this package needs no
 * edit to bootstrap/providers.php or to any module provider.
 */
class DemoSeederServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedDemoData::class,
            ]);
        }
    }
}
