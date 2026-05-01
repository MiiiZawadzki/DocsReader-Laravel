<?php

namespace Modules\Document\Concerns;

use Illuminate\Support\Facades\Auth;
use Modules\Document\Api\DocumentApiInterface;

trait ManagesOwnDocuments
{
    /**
     * @param  string  $routeParameter
     * @return bool
     */
    protected function userManagesRouteDocument(string $routeParameter = 'document'): bool
    {
        $documentUuid = $this->route($routeParameter);
        if (! is_string($documentUuid) || $documentUuid === '') {
            return false;
        }

        return app(DocumentApiInterface::class)->isManagerOf(Auth::id(), $documentUuid);
    }
}
