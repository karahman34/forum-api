<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RefreshNotificationEvent extends Event implements ShouldBroadcast
{
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function broadcastAs()
    {
        return 'refresh';
    }

    public function broadcastOn()
    {
        return new PrivateChannel("notifications.{$this->user->id}");
    }
}
