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
        $posts = Post::with(['user', 'comments'])->orderBy('id', 'desc')->get();

        return new PostCollection($posts);
    }

    public function store(): JsonResponse
    {
        $user = auth()->user();

        $data = request()->validate([
            'title' => 'required',
            'description' => 'required',
            'files' => 'array|max:9',
            'files.*' => 'file|max:2048'
        ]);

        $data['user_id'] = $user->id;

        $post = Post::create($data);

        $fileUrls = [];

        if (isset($data['files'])) {
            foreach ($data['files'] as $file) {
                $filePath = $file->store('images');

                $fileRecord = $post->files()->create([
                    'path' => asset('storage/' . $filePath)
                ]);

                $fileUrls[] = $fileRecord->path;
            }
        }

        return response()->json($post, $fileUrls);
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
