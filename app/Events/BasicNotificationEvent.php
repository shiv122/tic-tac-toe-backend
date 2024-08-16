<?php

namespace App\Events;

use App\Enums\NotificationType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class BasicNotificationEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public NotificationType $type,
        public string $title,
        public string $body,
        public string $icon = "",
        public string $tag = 'notification',
        public bool $renotify = true,
        public string $onClick = "",

    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('basic-notification-channel'),
        ];
    }
    public function broadcastAs(): string
    {
        return 'notification';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type->value,
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'tag' => $this->tag,
            'renotify' => $this->renotify,
            'onClick' => $this->onClick,
        ];
    }
}
