<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrivateUserEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $userId;
    public $message;
    public $data;

    public function __construct($userId, $message, $data = [])
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->data = $data;

        Log::channel('broadcasting')->info('🎭 EVENT_CREATED', [
            'user_id' => $userId,
            'message' => $message,
            'channel' => 'private-user.' . $userId
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('private-user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'private-user-event';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'data' => $this->data,
            'user_id' => $this->userId,
            'timestamp' => now()->toISOString(),
            'event_id' => uniqid()
        ];
    }
}