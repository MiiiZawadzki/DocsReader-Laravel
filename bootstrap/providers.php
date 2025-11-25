<?php

return [
    App\Providers\AppServiceProvider::class,
    \Modules\Auth\Providers\AuthServiceProvider::class,
    \Modules\Access\Providers\AccessServiceProvider::class,
    \Modules\User\Providers\UserServiceProvider::class,
    \Modules\Dashboard\Providers\DashboardServiceProvider::class,
    \Modules\Document\Providers\DocumentServiceProvider::class,
    \Modules\History\Providers\HistoryServiceProvider::class,
    \Modules\Analytics\Providers\AnalyticsServiceProvider::class,
];
