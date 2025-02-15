<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(): JsonResponse
    {
        $data = request()->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'content' => 'required|string|max:10000',
            'code' => 'required|string|max:10000',
        ]);

        $data['user_id'] = auth()->id();

        $comment = Comment::create($data);

        return response()->json($comment);
    }
}
