<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::controller(AuthController::class)->group(function() {
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');
        Route::post('/logout', 'logout')->middleware('auth:api')->name('logout');
        Route::post('/refresh', 'refresh')->middleware('auth:api')->name('refresh');
        Route::post('/me', 'me')->middleware('auth:api')->name('me');
    });

    Route::controller(SubscriptionController::class)->group(function() {
       Route::post('/subscribe/{userId}', 'subscribe')->name('subscribe');
       Route::post('/unsubscribe/{userId}', 'unsubscribe')->name('unsubscribe');
       Route::get('/subscriptions', 'getSubscriptions')->name('subscriptions');
       Route::get('/subscribers', 'getSubscribers')->name('subscribers');
    });
});

Route::controller(PostController::class)->group(function () {
    Route::get('/posts', 'index')->name('posts.index');
    Route::post('/posts', 'store')->name('posts.store');
    Route::patch('/posts/{post}', 'update')->name('posts.update');
    Route::delete('/posts/{post}', 'destroy')->name('posts.destroy');
});

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index')->name('users.index');
});
