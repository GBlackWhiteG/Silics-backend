<?php

namespace App\Providers;

use App\Events\LikedPost;
use App\Listeners\SendLikedNotification;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Event::listen(
            LikedPost::class,
            SendLikedNotification::class
        );
    }
}
