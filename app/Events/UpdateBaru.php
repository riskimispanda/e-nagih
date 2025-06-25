<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateBaru implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The data that was updated.
     *
     * @var array
     */
    public $data;

    /**
     * The type of notification.
     *
     * @var string
     */
    public $type;

    /**
     * The message for the notification.
     *
     * @var string
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param string $type
     * @param string $message
     */
    public function __construct(array $data, string $type = 'info', string $message = 'Data telah diperbarui')
    {
        $this->data = $data;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('updates-data'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'data.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'data' => $this->data,
            'notification' => [
                'type' => $this->type,
                'message' => $this->message,
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
