<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'name',
        'color',
    ];

    /**
     * Get the team that owns the event type.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the events for the event type.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
