<?php

namespace App\Http\Controllers;

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
        } else {
            $post->likes()->attach($userId);
            $message = 'Liked';
        }

        $likes_count = $post->likes()->count();
        $post->update(['likes' => $likes_count]);

        return response()->json(['message' => $message], 200);
    }
}
