<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'team_id',
        'color',
        'is_all_day',
    ];

    /**
     * Get the team that owns the event type.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
