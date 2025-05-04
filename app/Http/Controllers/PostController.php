<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\PostMessagesResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(): PostCollection
    {
        $order_by = request()->query('order_by');

        if (!in_array($order_by, ['created_at', 'likes'])) $order_by = 'created_at';

        $posts = Post::with(['user', 'comments', 'files'])->orderBy($order_by, 'desc')->orderBy('created_at', 'desc')->paginate(2);

        return new PostCollection($posts);
    }

    public function store(): JsonResponse
    {
        $user = auth()->user();

        $data = request()->validate([
            'title' => '',
            'description' => '',
            'code' => '',
            'prog_language' => '',
            'files' => 'array|max:9',
            'files.*' => 'mimes:jpg,jpeg,png,gif,webp|max:2048'
        ]);

        $data['user_id'] = $user->id;

        return DB::transaction(function () use ($data) {
            $post = Post::create($data);
            $fileUrls = [];

            if (isset($data['files'])) {

                foreach ($data['files'] as $file) {
                    $filePath = $file->storeAs('images', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');

                    $fileRecord = $post->files()->create([
                        'file_url' => asset('storage/' . $filePath)
                    ]);

                    $fileUrls[] = $fileRecord->path;
                }

            }

            return response()->json([
                'post' => $post,
                'file_urls' => $fileUrls
            ]);
        });
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post);
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

        return response()->json(['message' => 'Post successfully deleted']);
    }

    public function search(): PostCollection | JsonResponse
    {
        $search = request()->query('query');

        if (!$search) {
            return response()->json(['message' => 'Empty query param'], 400);
        }

        $posts = Post::where('title', 'like', '%' . $search . '%')->with(['user', 'comments', 'files'])->orderBy('id', 'desc')->get();

        return new PostCollection($posts);
    }
}
