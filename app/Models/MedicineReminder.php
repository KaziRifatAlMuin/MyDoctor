<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'reminder_at',
        'status',
        'taken_at',
    ];

    protected $casts = [
        'reminder_at' => 'datetime',
        'taken_at'    => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(MedicineSchedule::class, 'schedule_id');
    }
}
