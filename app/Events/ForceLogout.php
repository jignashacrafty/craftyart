<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ForceLogout implements ShouldBroadcastNow {

    use SerializesModels;
    public $userId;
    public $deviceId;

    public function __construct($userId, $deviceId)
    {
        $this->userId = $userId;
        $this->deviceId = $deviceId;
        Log::info('[EVENT] Force Logout::__construct', [
            'userId' => $userId,
            'deviceId' => $deviceId,
            'time' => now()->toISOString(),
        ]);
    }

    public function broadcastOn()
    {
        $channels = [ new PrivateChannel('user-' . $this->userId) ];
        return $channels;
    }

    public function broadcastAs()
    {
        return 'force.logout';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'userId' => $this->userId,
            'deviceId' => $this->deviceId,
            'message' => 'You have been logged out from another device.',
            'timestamp' => now()->toISOString(),
            'type' => 'force_logout'
        ];
        return $payload;
    }
}