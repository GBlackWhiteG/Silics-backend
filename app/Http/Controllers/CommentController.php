<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index(int $id): CommentCollection
    {
        $comments = Comment::where('post_id', $id)->orderBy('created_at', 'desc')->paginate(10);

        return new CommentCollection($comments);
    }

    public function store(): CommentResource | JsonResponse
    {
        $validator = Validator::make(request()->all(), ([
            'post_id' => 'required|integer|exists:posts,id',
            'content' => 'required|string|max:10000',
            'code' => 'nullable|string|max:10000',
            'prog_language' => '',
            'files' => 'array|max:9',
            'files.*' => 'mimes:jpg,jpeg,png,gif,webp|max:2048',
            'attachments' => 'array|max:9',
            'attachments.*' => 'file|mimes:txt,md,log,php,js,ts,html,css,scss,java,py,cpp,c,h,cs,go,rb,rs,sh,json,xml,yml,yaml,sql,ini,bat,cmd,ps1,kt,swift,doc,docx,xls,xlsx,ppt,pptx,pdf,rtf|max:5120',
        ]));

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $validator->validated();

        $data['user_id'] = auth()->id();

        return DB::transaction(function () use ($data) {
            $comment = Comment::create($data);

            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $filePath = $file->storeAs('images', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');

                    $fileRecord = $comment->files()->create([
                        'file_url' => asset('storage/' . $filePath),
                    ]);
                }

            }

            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $attachmentPath = $attachment->storeAs('attachments', uniqid() . '.' . $attachment->getClientOriginalExtension(), 'public');

                    $attachmentRecord = $comment->attachments()->create([
                        'original_filename' => $attachment->getClientOriginalName(),
                        'attachment_url' => asset('storage/' . $attachmentPath),
                        'mime_type' => $attachment->getClientMimeType(),
                    ]);
                }

            }

            return new CommentResource($comment);
        });
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment successfully deleted']);
    }
}
