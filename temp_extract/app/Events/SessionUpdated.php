<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SessionUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $userId;
    public $action;
    public $deviceId;
    public $data;
    public $email;

    public function __construct($userId, $action, $deviceId = null, $data = [], $email = null)
    {
        $this->userId = $userId;
        $this->action = $action;
        $this->deviceId = $deviceId;
        $this->data = $data;
        $this->email = $email;
    }

    public function broadcastOn()
    {
        $channels = [
            new PrivateChannel('user-' . $this->userId)
        ];

        if ($this->email) {
            $channels[] = new PrivateChannel('session-' . $this->email);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'session.updated';
    }

    public function broadcastWith()
    {
        return [
            'userId' => $this->userId,
            'action' => $this->action,
            'deviceId' => $this->deviceId,
            'data' => $this->data,
            'email' => $this->email,
            'timestamp' => now()->toISOString(),
            'type' => 'session_updated'
        ];
    }
}