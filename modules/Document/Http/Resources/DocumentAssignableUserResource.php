<?php

namespace Modules\Document\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Document\DTO\DocumentAssignableUserDTO;

class DocumentAssignableUserResource extends JsonResource
{
    public function __construct(DocumentAssignableUserDTO $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DocumentAssignableUserDTO $item */
        $item = $this->resource;

        return [
            'id' => $item->user->id,
            'name' => $item->user->name,
            'assign' => $item->assigned,
        ];
    }
}
