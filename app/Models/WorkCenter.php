<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
