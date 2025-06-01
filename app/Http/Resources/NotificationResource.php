<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->notified_id);

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'likedUserId' => $this->notified_id,
            'likedUserAvatar' => $user->avatar_url,
            'postId' => $this->post_id,
            'notificationableId' => $this->notificationable_id,
            'notificationableType' => $this->notificationable_type,
            'message' => $this->message,
        ];
    }
}
