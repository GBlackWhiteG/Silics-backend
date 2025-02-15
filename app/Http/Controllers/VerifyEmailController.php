<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerifyEmailController extends Controller
{
    public function verify(int $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Неверный токен'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email уже подтвержден']);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        $token = auth()->login($user);

        return response()->json(['token' => $token, 'message' => 'Email подтвержден']);
    }

    public function verify2FA(Request $request): JsonResponse
    {
        $data = request()->validate([
            'code' => 'required|string',
        ]);

        $userId = Cache::get('2fa_user_id');

        if (!$userId) {
            return response()->json(['message' => 'Время сессии истекло']);
        }

        $code = Cache::get('2fa_code_' . $userId);

        if ($data['code'] != $code) {
            return response()->json(['message' => 'Неверный код'], 404);
        }

        Cache::forget('2fa_code_' . $userId);
        session()->forget('2fa_user_id');

        $user = User::find($userId);
        $token = auth()->login($user);

        return response()->json(['token' => $token]);
    }
}
