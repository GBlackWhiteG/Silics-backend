<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostMessagesResource extends JsonResource
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
            'posted_ago' => (int)Carbon::parse($this->created_at)->diffInMinutes(Carbon::now()),
            'user_name' => $this->user->name,
            'files'=> $this->files,
            'likes' => $this->likes,
            'liked_by_user' => $this->isLikedByUser($currentUserId),
            'comments_count' => count($this->comments),
            'comments' => CommentResource::collection($this->comments),
        ];
    }
}
