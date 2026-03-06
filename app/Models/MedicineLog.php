<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineLog extends Model
{
    use HasFactory;

    protected $table = 'medicine_logs';
    protected $primaryKey = 'LogID';
    public $timestamps = false;

    protected $fillable = [
        'MedicineID',
        'UserID',
        'Date',
        'TotalScheduled',
        'TotalTaken',
        'TotalMissed'
    ];

    protected $casts = [
        'Date' => 'date',
    ];

    /**
     * Get the medicine that owns the log.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'MedicineID', 'MedicineID');
    }

    /**
     * Get the user that owns the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    /**
     * Get adherence rate percentage.
     */
    public function getAdherenceRateAttribute()
    {
        if ($this->TotalScheduled === 0) {
            return 0;
        }
        return round(($this->TotalTaken / $this->TotalScheduled) * 100, 2);
    }

    /**
     * Get adherence rate with color class.
     */
    public function getAdherenceStatusAttribute()
    {
        $rate = $this->adherenceRate;
        
        if ($rate >= 90) {
            return ['class' => 'success', 'text' => 'Excellent'];
        } elseif ($rate >= 75) {
            return ['class' => 'info', 'text' => 'Good'];
        } elseif ($rate >= 50) {
            return ['class' => 'warning', 'text' => 'Fair'];
        } else {
            return ['class' => 'danger', 'text' => 'Poor'];
        }
    }
}