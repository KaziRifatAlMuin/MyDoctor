<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'division_id',
        'division',
        'division_bn',
        'district_id',
        'district',
        'district_bn',
        'upazila_id',
        'upazila',
        'upazila_bn',
        'street',
        'house',
    ];

    protected $casts = [
        'division_id' => 'integer',
        'district_id' => 'integer',
        'upazila_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayDivisionAttribute(): string
    {
        if (app()->getLocale() === 'bn' && !empty($this->division_bn)) {
            return $this->division_bn;
        }

        return $this->division ?: 'Not set';
    }

    public function getDisplayDistrictAttribute(): string
    {
        if (app()->getLocale() === 'bn' && !empty($this->district_bn)) {
            return $this->district_bn;
        }

        return $this->district ?: 'Not set';
    }

    public function getDisplayUpazilaAttribute(): string
    {
        if (app()->getLocale() === 'bn' && !empty($this->upazila_bn)) {
            return $this->upazila_bn;
        }

        return $this->upazila ?: 'Not set';
    }

    public function getDisplayAddressAttribute(): string
    {
        $parts = [];

        if (!empty($this->house)) {
            $parts[] = $this->house;
        }

        if (!empty($this->street)) {
            $parts[] = $this->street;
        }

        $parts[] = $this->display_upazila;
        $parts[] = $this->display_district;

        return implode(', ', array_filter($parts));
    }
}
