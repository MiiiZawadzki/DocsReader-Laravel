<?php

namespace Modules\Document\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Document\DTO\DocumentListItemDTO;

class DocumentResource extends JsonResource
{
    public function __construct(DocumentListItemDTO $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var DocumentListItemDTO $item */
        $item = $this->resource;
        $document = $item->document;

        return [
            'id' => $document->getAttribute('uuid'),
            'name' => $document->getAttribute('name'),
            'description' => $document->getAttribute('description'),
            'status' => DocumentStatusResource::make($item->readAt),
            'userTag' => $item->authorTag,
            'authorId' => $document->getAttribute('user_id'),
            'dateTag' => $document->getAttribute('date_from')->format('Y-m-d'),
            'requiresConfirmation' => $document->getAttribute('requires_confirmation') ?? false,
            'buttonText' => 'Go to document',
        ];
    }
}
