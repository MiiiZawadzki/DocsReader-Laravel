<?php

namespace Modules\History\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\History\DTO\HistoryListItemDTO;

class HistoryItemResource extends JsonResource
{
    public function __construct(HistoryListItemDTO $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var HistoryListItemDTO $item */
        $item = $this->resource;
        $document = $item->document;

        return [
            'id' => $document->uuid,
            'name' => $document->name,
            'description' => $document->description,
            'status' => HistoryStatusResource::make($item->readAt),
            'userTag' => $item->authorTag,
            'authorId' => $document->userId,
            'dateTag' => $document->dateFrom->format('Y-m-d'),
        ];
    }
}
