<?php

namespace Modules\Analytics\Http\Requests;

class DocumentEngagementSessionsRequest extends DocumentEngagementRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
