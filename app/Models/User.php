<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\EmailCodeNotification;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nickname',
        'biography',
        'avatar_url',
        'password',
        'is_enabled_two_fa'
    ];

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 'subscriptions', 'subscriber_id', 'subscribed_to_id'
        );
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new EmailCodeNotification());
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 'subscriptions', 'subscribed_to_id', 'subscriber_id'
        );
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
