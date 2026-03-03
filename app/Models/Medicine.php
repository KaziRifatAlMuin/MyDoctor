<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medicine_name',
        'type',
        'value_per_dose',
        'unit',
        'rule',
        'dose_limit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    public function logs()
    {
        return $this->hasMany(MedicineLog::class);
    }
}
