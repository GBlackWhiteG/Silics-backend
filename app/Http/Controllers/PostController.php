<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(): PostCollection
    {
        $posts = Post::with('user')->orderBy('id', 'desc')->get();

        return new PostCollection($posts);
    }

    public function store(): JsonResponse
    {
        $data = request()->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required',
            'description' => 'required'
        ]);

        $post = Post::create($data);

        return response()->json($post);
    }

    public function update(Post $post): JsonResponse
    {
        $data = request()->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required',
            'description' => 'required'
        ]);

        $post->update($data);

        return response()->json($data);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(['message' => 'Successfully deleted']);
    }
}
