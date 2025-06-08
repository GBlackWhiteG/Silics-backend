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

class AddCommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public int $userId;
    public int $likedUserId;
    public string $likedUserAvatar;
    public int $postId;
    public string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, int $postId, int $commentId, User $likedUser)
    {
        $message = "Пользователь {$likedUser->name} прокомментировал вашу публикацию";

        $notification = Notification::create([
            'user_id' => $userId,
            'notified_id' => $likedUser->id,
            'post_id' => $postId,
            'message' => $message,
            'notificationable_id' => $commentId,
            'notificationable_type' => Comment::class,
        ]);

        $this->id = $notification->id;
        $this->userId = $userId;
        $this->likedUserId = $likedUser->id;
        $this->likedUserAvatar = $likedUser->avatar_url || null;
        $this->postId = $postId;
        $this->message = $message;
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
