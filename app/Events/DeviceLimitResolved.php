<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class DeviceLimitResolved implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('session-' . $this->email);
    }

    public function broadcastAs()
    {
        return 'device.limit.resolved';
    }

    public function broadcastWith()
    {
        return [
            'email' => $this->email,
            'message' => 'Device limit has been resolved',
            'timestamp' => now()->toISOString()
        ];
    }
}