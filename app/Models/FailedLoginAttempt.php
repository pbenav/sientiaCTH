<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'timestamp',
        'lockout_time',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
