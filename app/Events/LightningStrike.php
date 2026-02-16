<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class LightningStrike implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public $strike;

    public function __construct($strike)
    {
        $this->strike = $strike;
    }

    public function broadcastOn()
    {
        return new Channel('lightning');
    }

    public function broadcastAs()
    {
        return 'new-strike';
    }
}
