<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnlikeEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, int $postId)
    {
        $notification = Notification::where(['user_id' => $userId, 'post_id' => $postId, 'notificationable_type' => 'like'])->first();

        $this->id = $notification->id ?? 0;
        $this->userId = $userId;

        $notification?->delete();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notification.' . $this->userId),
        ];
    }
}
