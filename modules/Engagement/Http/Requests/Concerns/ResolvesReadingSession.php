<?php

namespace Modules\Engagement\Http\Requests\Concerns;

use Modules\Engagement\Models\ReadingSession;
use Modules\Engagement\Repositories\Contracts\ReadingSessionRepositoryInterface;

trait ResolvesReadingSession
{
    private ?ReadingSession $resolvedSession = null;

    private bool $sessionResolved = false;

    /**
     * Resolve the route's reading session once and memoize it, so authorize()
     * and the controller don't each pay a separate lookup for the same request.
     *
     * @return ReadingSession|null
     */
    public function readingSession(): ?ReadingSession
    {
        if (! $this->sessionResolved) {
            $this->resolvedSession = app(ReadingSessionRepositoryInterface::class)
                ->findByUuid((string) $this->route('uuid'));
            $this->sessionResolved = true;
        }

        return $this->resolvedSession;
    }
}
