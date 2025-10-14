<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'code',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
