<?php

use App\Http\Controllers\CommentController;
use App\Mail\VerificationMail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\VerifyEmailController;

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');
        Route::post('/logout', 'logout')->middleware('auth:api')->name('logout');
        Route::post('/refresh', 'refresh')->middleware('auth:api')->name('refresh');
        Route::post('/me', 'me')->middleware('auth:api')->name('me');

        Route::post('/send-confirmation-code', 'sendConfirmationCode');
    });

    Route::controller(SubscriptionController::class)->group(function () {
        Route::post('/subscribe/{userId}', 'subscribe')->name('subscribe');
        Route::post('/unsubscribe/{userId}', 'unsubscribe')->name('unsubscribe');
        Route::get('/subscriptions', 'getSubscriptions')->name('subscriptions');
        Route::get('/subscribers', 'getSubscribers')->name('subscribers');
    });

    Route::post('/likes', [LikeController::class, 'like'])->name('like');

    Route::controller(CommentController::class)->group(function () {
        Route::post('/comments', 'store')->name('comments.store');
    });
});

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware('signed', 'throttle:6,1')->name('verification.verify');

Route::post('/email/2fa', [VerifyEmailController::class, 'verify2FA'])->middleware('throttle:6,1')->name('verification.verify2fa');

Route::controller(PostController::class)->group(function () {
    Route::get('/posts', 'index')->name('posts.index');
    Route::post('/posts', 'store')->name('posts.store');
    Route::get('/posts/{post}', 'show')->name('posts.show');
    Route::patch('/posts/{post}', 'update')->name('posts.update');
    Route::delete('/posts/{post}', 'destroy')->name('posts.destroy');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index')->name('users.index');
});
