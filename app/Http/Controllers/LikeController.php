<?php

namespace App\Http\Controllers;

use App\Events\LikeEvent;
use App\Events\UnlikeEvent;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function like(): JsonResponse
    {
        $data = request()->validate([
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $userId = auth()->id();
        $post = Post::where('id', $data['post_id'])->first();

        if ($post->likes()->where('like_posts.user_id', $userId)->exists()) {
            $post->likes()->detach($userId);
            $message = 'Unliked';
            if ($post->user_id !== $userId) {
                event(new UnlikeEvent($post->user->id, $post->id));
            }
        } else {
            $post->likes()->attach($userId);
            $message = 'Liked';
            if ($post->user_id !== $userId) {
                event(new LikeEvent($post->user->id, $post->id, auth()->user()));
            }
        }

        $likes_count = $post->likes()->count();
        $post->update(['likes' => $likes_count]);

        return response()->json(['message' => $message], 200);
    }
}
