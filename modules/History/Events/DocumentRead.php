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
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("history.documents.{$this->documentUuid}");
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
        ];
    }
}
