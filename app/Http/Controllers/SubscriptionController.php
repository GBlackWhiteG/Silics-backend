<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

class SubscriptionController extends Controller
{
    public function subscribe($userId): JsonResponse
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

    public function unsubscribe($userId): JsonResponse
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

    public function getSubscriptions(): JsonResponse
    {
        $user = auth()->user();

        return response()->json($user->subscriptions);
    }

    public function getSubscribers(): JsonResponse
    {
        $user = auth()->user();

        return response()->json($user->subscribers);
    }
}
