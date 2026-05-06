<?php

namespace Modules\Document\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentAssignmentChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $documentUuid,
        public readonly string $documentName,
        public readonly int $userId,
        public readonly bool $assigned,
        public readonly int $changedById,
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("users.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'document.assignment.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'documentUuid' => $this->documentUuid,
            'documentName' => $this->documentName,
            'assigned' => $this->assigned,
            'changedById' => $this->changedById,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
