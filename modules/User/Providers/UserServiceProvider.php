<?php

namespace Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\User\Api\UserApi;
use Modules\User\Api\UserApiInterface;
use Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Modules\User\Repositories\UserRepository;
use Route;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserApiInterface::class, UserApi::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'user');


        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');
    }
}
