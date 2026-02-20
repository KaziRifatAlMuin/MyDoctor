<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'UserID';
    public $timestamps = false;

    protected $fillable = [
        'Picture',
        'Name',
        'DateOfBirth',
        'Phone',
        'Email',
        'Occupation',
        'BloodGroup',
        'CreatedAt',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'DateOfBirth' => 'date',
        'CreatedAt' => 'datetime',
        'password' => 'hashed',
    ];
}