<?php

namespace App\Events;

use App\Models\Comment;
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

class RemoveCommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, int $notifiedId, int $commentId)
    {
        $notification = Notification::where(['notified_id' => $notifiedId, 'notificationable_id' => $commentId, 'notificationable_type' => Comment::class])->first();

        $this->id = $notification->id ?? 0;
        $this->userId = $userId;

        $notification?->delete(); // типо if (isset($notification)) $notification->delete();
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
