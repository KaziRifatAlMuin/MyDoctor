<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'user_id',
        'date',
        'total_scheduled',
        'total_taken',
        'total_missed',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
