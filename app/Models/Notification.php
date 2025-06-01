<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'notified_id', 'post_id', 'message', 'notificationable_id', 'notificationable_type'];
}
