<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('notification.{id}', function ($user) {
    return (int) auth()->id() === (int) $user->id;
});
