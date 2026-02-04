<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOffline implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $deviceId;

    public function __construct($userId, $deviceId)
    {
        $this->userId = $userId;
        $this->deviceId = $deviceId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user-status.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'UserOffline';
    }
}
