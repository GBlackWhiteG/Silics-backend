<?php

namespace App\Http\Controllers;

use App\Mail\VerificationMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Random\RandomException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function register(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = DB::transaction(function() {
            $user = new User;
            $user->name = request()->name;
            $user->email = request()->email;
            $user->password = bcrypt(request()->password);
            $user->nickname = "user" . rand(1, 100);
            $user->save();

            event(new Registered($user));

            return $user;
        }, 3);

        return response()->json($user, 200);
    }

    /**
     * @throws RandomException
     */
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        if ($user->blocked) {
            return response()->json(['error' => 'Пользователь заблокирован'], 401);
        }

        if (!$user->is_enabled_two_fa) {
            $token = auth()->login($user);
            return response()->json(['token' => $token], 200);
        }

        Cache::put('2fa_user_id', auth()->user()->id);

        $code = random_int(100000, 999999);
        Cache::put('2fa_code_' . auth()->user()->id, $code, now()->addMinutes(10));

        Mail::to($credentials['email'])->send(new VerificationMail($code));

        return response()->json(['message' => '2FA code sent']);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth('api')->user());
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
