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
        'total_missed'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the medicine that owns the log.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the user that owns the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get adherence rate percentage.
     */
    public function getAdherenceRateAttribute()
    {
        if ($this->total_scheduled === 0) {
            return 0;
        }
        return round(($this->total_taken / $this->total_scheduled) * 100, 2);
    }

    /**
     * Get adherence rate with translated status text.
     */
    public function getAdherenceStatusAttribute()
    {
        $rate = $this->adherenceRate;
        
        if ($rate >= 90) {
            return ['class' => 'success', 'text' => __('ui.medicine.status_excellent')];
        } elseif ($rate >= 75) {
            return ['class' => 'info', 'text' => __('ui.medicine.status_good')];
        } elseif ($rate >= 50) {
            return ['class' => 'warning', 'text' => __('ui.medicine.status_fair')];
        } else {
            return ['class' => 'danger', 'text' => __('ui.medicine.status_poor')];
        }
    }
}