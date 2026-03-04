<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'file_path',
        'summary',
        'notes',
        'doctor_name',
        'institution',
        'document_date',
    ];

    protected $casts = [
        'document_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
