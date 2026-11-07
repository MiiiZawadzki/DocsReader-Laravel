<?php

namespace Modules\Engagement\Http\Requests\Concerns;

trait ValidatesTicks
{
    /**
     * @param  string  $presence  'required' for a tick batch, 'sometimes' where ticks are optional
     * @return array
     */
    private function tickRules(string $presence): array
    {
        return [
            'ticks' => [$presence, 'array', 'min:1', 'max:200'],
            'ticks.*.clientEventId' => ['required', 'string', 'size:26'],
            'ticks.*.pageNumber' => ['required', 'integer', 'min:1'],
            'ticks.*.activeMs' => ['required', 'integer', 'min:1'],
            'ticks.*.occurredAt' => ['nullable', 'string'],
        ];
    }
}
