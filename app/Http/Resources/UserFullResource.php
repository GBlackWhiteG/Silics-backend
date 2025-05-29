<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = auth()->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'email' => $this->email,
            'biography' => $this->biography,
            'avatar_url' => $this->avatar_url,
            'email_verified_at' => $this->email_verified_at,
            'subscriptions_count' => count($this->subscriptions),
            'subscribers_count' => count($this->subscribers),
            'is_subscribed' => $authUser ? $authUser->subscriptions->contains($this->resource->id) : false,
            'is_blocked' => $this->blocked,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
