<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'disease_name',
        'disease_name_bn',
        'description',
    ];

    /**
     * Get the posts for this disease
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the users who have this disease
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_diseases')
                    ->withPivot('diagnosed_at', 'status', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the user diseases pivot records
     */
    public function userDiseases()
    {
        return $this->hasMany(UserDisease::class);
    }
}