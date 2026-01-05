<?php

namespace Modules\Document\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Document\Api\DocumentApi;
use Modules\Document\Api\DocumentApiInterface;
use Modules\Document\Repositories\Contracts\DocumentRepositoryInterface;
use Modules\Document\Repositories\Contracts\UserDocumentRepositoryInterface;
use Modules\Document\Repositories\DocumentRepository;
use Modules\Document\Repositories\UserDocumentRepository;
use Route;

class DocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DocumentRepositoryInterface::class, DocumentRepository::class);
        $this->app->bind(UserDocumentRepositoryInterface::class, UserDocumentRepository::class);
        $this->app->bind(DocumentApiInterface::class, DocumentApi::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Lang', 'document');

        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
