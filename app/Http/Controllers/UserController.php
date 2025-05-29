<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserFullCollection;
use App\Http\Resources\UserFullResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(): UserFullCollection
    {
        $users = User::paginate(10);

        return new UserFullCollection($users);
    }

    public function getProfile(User $user): UserFullResource
    {
        return new UserFullResource($user);
    }

    public function update(User $user): UserResource | JsonResponse
    {
        if ($user->id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'required|',
            'nickname' => 'required|unique:users,nickname,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'biography' => 'nullable|string|max:512',
            'avatar' => 'mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = $validator->validated();

        if (isset($data['avatar'])) {
            $filaPath = $data['avatar']->storeAs('images', uniqid() . '.' . $data['avatar']->getClientOriginalExtension(), 'public');
            $data['avatar_url'] = asset('storage/' . $filaPath);
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function userIsBlockedChange(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user->blocked = !$user->blocked;
        $user->save();

        return response()->json(['message' => 'user blocked status changed'], 200);
    }
}
