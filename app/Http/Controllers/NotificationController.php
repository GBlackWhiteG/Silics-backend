<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationCollection;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function all(): NotificationCollection
    {
        return new NotificationCollection(Notification::all());
    }

    public function index(int $id): NotificationCollection
    {
        $notifications = Notification::where('user_id', $id)->take(5)->get();

        return new NotificationCollection($notifications);
    }
}
