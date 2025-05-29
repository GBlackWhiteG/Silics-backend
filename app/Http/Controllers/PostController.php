<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\PostMessagesResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(): PostCollection
    {
        $order_by = request()->query('order_by');

        if (!in_array($order_by, ['created_at', 'likes'])) $order_by = 'created_at';

        $posts = Post::with(['user', 'comments', 'files'])->orderBy($order_by, 'desc')->orderBy('created_at', 'desc')->paginate(10);

        return new PostCollection($posts);
    }

    public function userPosts(int $user_id): PostCollection
    {
        $posts = Post::where('user_id', $user_id)->with('user', 'comments', 'files')->orderBy('created_at', 'desc')->paginate(10);

        return new PostCollection($posts);
    }

    public function store(): PostResource | JsonResponse
    {
        $user = auth()->user();

        $validator = Validator::make(request()->all(), ([
            'title' => '',
            'description' => '',
            'code' => '',
            'prog_language' => '',
            'files' => 'array|max:9',
            'files.*' => 'mimes:jpg,jpeg,png,gif,webp|max:2048',
            'attachments' => 'array|max:9',
            'attachments.*' => 'file|max:5120',
        ]));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        $data['user_id'] = $user->id;

        return DB::transaction(function () use ($data) {
            $post = Post::create($data);

            if (isset($data['files'])) {

                foreach ($data['files'] as $file) {
                    $filePath = $file->storeAs('images', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');

                    $fileRecord = $post->files()->create([
                        'file_url' => asset('storage/' . $filePath)
                    ]);
                }

            }

            if (isset($data['attachments'])) {

                foreach ($data['attachments'] as $attachment) {
                    $attachmentPath = $attachment->storeAs('attachments', uniqid() . '.' . $attachment->getClientOriginalExtension(), 'public');

                    $attachmentRecord = $post->attachments()->create([
                        'original_filename' => $attachment->getClientOriginalName(),
                        'attachment_url' => asset('storage/' . $attachmentPath),
                        'mime_type' => $attachment->getClientMimeType(),
                    ]);
                }

            }

            return new PostResource($post);
        });
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post);
    }

    public function update(Post $post): JsonResponse | PostResource
    {
        $userId = auth()->id();

        if ($userId !== $post->user_id) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $validator = Validator::make(request()->all(), ([
            'title' => '',
            'description' => '',
            'code' => '',
            'prog_language' => '',
            'files' => 'array|max:9',
            'files.*' => 'mimes:jpg,jpeg,png,gif,webp|max:2048',
            'attachments' => 'array|max:9',
            'attachments.*' => 'file|mimes:txt,md,log,php,js,ts,html,css,scss,java,py,cpp,c,h,cs,go,rb,rs,sh,json,xml,yml,yaml,sql,ini,bat,cmd,ps1,kt,swift,doc,docx,xls,xlsx,ppt,pptx,pdf,rtf|max:5120',
            'delete_files' => 'array|max:9',
            'delete_files.*' => 'numeric|exists:files,id',
            'delete_attachments' => 'array|max:9',
            'delete_attachments.*' => 'numeric|exists:attachments,id'
        ]));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['files']) && $post->files()->count() + count($data['files']) >= 9 || isset($data['attachments']) && $post->attachments()->count() + count($data['attachments']) >= 9) {
            return response()->json(['error' => 'Слишком много изображений/вложений'], 400);
        }

        return DB::transaction(function () use ($data, $post) {
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $filePath = $file->storeAs('images', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');

                    $fileRecord = $post->files()->create([
                        'file_url' => asset('storage/' . $filePath)
                    ]);
                }
            }

            if (isset($data['delete_files'])) {
                foreach ($data['delete_files'] as $fileId) {
                    $file = $post->files()->find($fileId);

                    Storage::delete($file->file_url);

                    $file->delete();
                }
            }

            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $attachmentPath = $attachment->storeAs('attachments', uniqid() . '.' . $attachment->getClientOriginalExtension(), 'public');

                    $attachmentRecord = $post->attachments()->create([
                        'original_filename' => $attachment->getClientOriginalName(),
                        'attachment_url' => asset('storage/' . $attachmentPath),
                        'mime_type' => $attachment->getClientMimeType(),
                    ]);
                }
            }

            if (isset($data['delete_attachments'])) {
                foreach ($data['delete_attachments'] as $attachmentId) {
                    $attachment = $post->attachments()->find($attachmentId);

                    Storage::delete($attachment->attachment_url);

                    $attachment->delete();
                }
            }

            $post->update($data);

            return new PostResource($post);
        });
    }

    public function destroy(Post $post): JsonResponse
    {
        if ($post->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 402);
        }

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
