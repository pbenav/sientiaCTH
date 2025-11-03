<?php

namespace App\Models;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

/**
 * Represents a team of users.
 *
 * This model extends the base Jetstream team model to include custom
 * relationships and properties specific to the application.
 */
class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * Get the event types associated with the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventTypes()
    {
        return $this->hasMany(EventType::class);
    }

    /**
     * Get the work centers associated with the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workCenters()
    {
        return $this->hasMany(WorkCenter::class);
    }

    /**
     * Get the holidays associated with the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
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
     * @var array<int, string>
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
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the announcements for the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function announcements()
    {
        return $this->hasMany(TeamAnnouncement::class);
    }
}
