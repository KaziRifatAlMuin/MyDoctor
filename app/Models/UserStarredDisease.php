<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStarredDisease extends Model
{
    use HasFactory;

    protected $table = 'user_starred_diseases';

    protected $fillable = [
        'user_id',
        'disease_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }
}
