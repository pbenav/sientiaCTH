<?php

namespace App\Models;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    public function eventTypes()
    {
        return $this->hasMany(EventType::class);
    }

    public function workCenters()
    {
        return $this->hasMany(WorkCenter::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'personal_team' => 'boolean',
        'force_clock_in_delay' => 'boolean',
        'clock_in_delay_minutes' => 'integer',
        'clock_in_grace_period_minutes' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'personal_team',
        'force_clock_in_delay',
        'clock_in_delay_minutes',
        'clock_in_grace_period_minutes',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];
}
