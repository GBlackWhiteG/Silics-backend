<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFullCollection;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubscriptionController extends Controller
{
    public function subscribe(int $userId): JsonResponse
    {
        $user = auth()->user();

        if (!isset($user)) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        if ($user->subscriptions()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Уже подписан'], 400);
        }

        $user->subscriptions()->attach($userId);

        return response()->json(['message' => 'Успешно подписан']);
    }

    public function unsubscribe(int $userId): JsonResponse
    {
        $user = auth()->user();

        if (!isset($user)) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        if (!$user->subscriptions()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Не подписан'], 400);
        }

        $user->subscriptions()->detach($userId);

        return response()->json(['message' => 'Успешно отписан']);
    }

    public function getSubscriptions(): UserFullCollection
    {
        $user = auth()->user();

        $data = $user->subscriptions()->paginate(10);

        return new UserFullCollection($data);
    }

    public function getSubscribers(): UserFullCollection
    {
        $user = auth()->user();

        $data = $user->subscribers()->paginate(10);

        return new UserFullCollection($data);
    }
}
