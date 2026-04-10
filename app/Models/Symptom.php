<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function userSymptoms()
    {
        return $this->hasMany(UserSymptom::class);
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'disease_symptoms')
            ->withTimestamps();
    }
}
