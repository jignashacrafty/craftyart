<?php

namespace App\Events;

use App\Models\DesignerApplication;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewDesignerApplication implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $application;

    public function __construct(DesignerApplication $application)
    {
        $this->application = $application;
    }

    public function broadcastOn()
    {
        return new Channel('designer-applications');
    }

    public function broadcastAs()
    {
        return 'new-application';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->application->id,
            'name' => $this->application->name,
            'email' => $this->application->email,
            'phone' => $this->application->phone,
            'city' => $this->application->city,
            'state' => $this->application->state,
            'experience' => $this->application->experience,
            'status' => $this->application->status,
            'created_at' => $this->application->created_at->format('d M Y'),
        ];
    }
}
