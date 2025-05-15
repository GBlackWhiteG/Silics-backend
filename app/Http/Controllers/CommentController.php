<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(int $id): CommentCollection
    {
        $comments = Comment::where('post_id', $id)->orderBy('created_at', 'desc')->paginate(5);

        return new CommentCollection($comments);
    }

    public function store(): CommentResource
    {
        $data = request()->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'content' => 'required|string|max:10000',
            'code' => 'nullable|string|max:10000',
            'prog_language' => '',
            'files' => 'array|max:9',
            'files.*' => 'mimes:jpg,jpeg,png,gif,webp|max:2048'
        ]);

        $data['user_id'] = auth()->id();

        return DB::transaction(function () use ($data) {
            $comment = Comment::create($data);
            $fileUrls = [];

            if (isset($data['files'])) {

                foreach ($data['files'] as $file) {
                    $filePath = $file->storeAs('images', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');

                    $fileRecord = $comment->files()->create([
                        'file_url' => asset('storage/' . $filePath),
                    ]);

                    $fileUrls[] = $fileRecord->path;
                }

            }

            return new CommentResource($comment);
        });
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment successfully deleted']);
    }
}
