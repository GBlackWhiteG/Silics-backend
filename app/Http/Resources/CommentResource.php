<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => (int)$this->post_id,
            'content' => $this->content,
            'code' => $this->code,
            'prog_language' => $this->prog_language,
            'files' => $this->files,
            'attachments' => $this->attachments,
            'posted_ago' => (int)Carbon::parse($this->created_at)->diffInMinutes(Carbon::now()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->user),
        ];
    }
}
