<?php

namespace Modules\History\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $documentUuid,
        public readonly string $documentName,
        public readonly int $userId,
        public readonly string $userName,
        public readonly string $readAt,
        public readonly int $totalReadCount,
        public readonly int $totalActiveSeconds = 0,
        public readonly int $pagesViewed = 0,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("history.documents.{$this->documentUuid}"),
            new PrivateChannel("users.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.read';
    }

    public function broadcastWith(): array
    {
        return [
            'documentUuid' => $this->documentUuid,
            'documentName' => $this->documentName,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'readAt' => $this->readAt,
            'totalReadCount' => $this->totalReadCount,
            'totalActiveSeconds' => $this->totalActiveSeconds,
            'pagesViewed' => $this->pagesViewed,
        ];
    }
}
