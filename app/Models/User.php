<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'qris',
    ];

    protected $appends = [
        'qris_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function getQrisUrlAttribute()
    {
        return $this->qris
            ? asset('storage/' . $this->qris)
            : null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}