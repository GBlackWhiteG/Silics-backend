<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUserId = auth()->id();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'code' => $this->code,
            'prog_language' => $this->prog_language,
            'created_at' => $this->created_at,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_avatar' => $this->user->avatar_url,
            'files' => $this->files,
            'attachments' => $this->attachments,
            'likes' => $this->likes,
            'liked_by_user' => $this->isLikedByUser($currentUserId),
            'comments_count' => count($this->comments),
        ];
    }
}
