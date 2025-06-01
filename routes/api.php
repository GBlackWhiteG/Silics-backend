<?php

use App\Http\Controllers\CodeResultController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExecuteCodeController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\VerifyEmailController;

Route::group(['middleware' => 'api'], function () {
    Route::group(['prefix' =>  'auth'], function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/register', 'register')->name('register');
            Route::post('/login', 'login')->name('login');

            Route::post('/send-confirmation-code', 'sendConfirmationCode');
        });
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::controller(AuthController::class)->group(function () {
                Route::post('/logout', 'logout')->middleware('auth:api')->name('logout');
                Route::post('/refresh', 'refresh')->name('refresh')->middleware('isUnblock');
                Route::post('/me', 'me')->middleware('auth:api')->name('me');
            });
        });

        Route::controller(SubscriptionController::class)->group(function () {
            Route::post('/subscribe/{userId}', 'subscribe')->name('subscribe');
            Route::post('/unsubscribe/{userId}', 'unsubscribe')->name('unsubscribe');
            Route::get('/subscriptions', 'getSubscriptions')->name('subscriptions');
            Route::get('/subscribers', 'getSubscribers')->name('subscribers');
        });

        Route::post('/likes', [LikeController::class, 'like'])->name('like');

        Route::controller(CommentController::class)->group(function () {
            Route::get('/comments/{id}', 'index')->name('comments.index');
            Route::post('/comments', 'store')->name('comments.store');
            Route::delete('/comments/{comment}', 'destroy')->name('comments.destroy');
        });

        Route::post('/code/execute', [ExecuteCodeController::class, 'sendCodeToQueue']);

        Route::get('/code/execution-result/{id}', [CodeResultController::class, 'get']);
        Route::post('/code/execution-result', [CodeResultController::class, 'add']);

        Route::controller(PostController::class)->group(function () {
            Route::post('/posts', 'store')->name('posts.store');
            Route::post('/posts/{post}', 'update')->name('posts.update');
            Route::delete('/posts/{post}', 'destroy')->name('posts.destroy');
        });

        Route::controller(UserController::class)->group(function () {
            Route::post('/users/{user}', 'update')->name('users.update');
            Route::put('/users/{user}', 'userIsBlockedChange');
        });

        Route::controller(NotificationController::class)->group(function () {
            Route::get('/notifications/{id}', 'index')->name('notifications');
        });
    });

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware('signed', 'throttle:6,1')->name('verification.verify');
    Route::post('/email/2fa', [VerifyEmailController::class, 'verify2FA'])->middleware('throttle:6,1')->name('verification.verify2fa');

    Route::controller(PostController::class)->group(function () {
        Route::get('/posts', 'index')->name('posts.index');
        Route::get('/posts/{post}', 'show')->name('posts.show');
        Route::get('/posts/user/{id}', 'userPosts')->name('posts.userPosts');
    });

    Route::get('/search', [PostController::class, 'search'])->name('search');

    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->name('users.index');
        Route::get('/users/{user}', 'getProfile')->name('users.getProfile');
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'all')->name('notifications');
    });
});
