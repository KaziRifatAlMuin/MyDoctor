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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_diseases')
                    ->withPivot('diagnosed_at', 'status', 'notes')
                    ->withTimestamps();
    }

    public function userDiseases()
    {
        return $this->hasMany(UserDisease::class);
    }
}
