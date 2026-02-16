<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

/**
 * Team Model
 * 
 * Represents a team/organization with custom configuration for time tracking,
 * event types, work centers, holidays, and reporting settings.
 *
 * @property int $id
 * @property int $user_id Owner user ID
 * @property string $name Team name
 * @property bool $personal_team Is this a personal team
 * @property string $pdf_engine PDF generation engine (browsershot)
 * @property int $max_report_months Maximum months for report generation
 * @property int $async_report_threshold_months Threshold for async reports
 * @property int $event_retention_months Event retention in months
 * @property string $timezone Team timezone (default: Europe/Madrid)
 * @property int|null $event_expiration_days Days before events expire
 * @property bool $force_clock_in_delay Require clock-in delay
 * @property int|null $clock_in_delay_minutes Clock-in delay in minutes
 * @property int|null $clock_in_grace_period_minutes Grace period minutes
 * @property string|null $special_event_color Color for special events
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<EventType> $eventTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<WorkCenter> $workCenters
 * @property-read \Illuminate\Database\Eloquent\Collection<Holiday> $holidays
 * @property-read \Illuminate\Database\Eloquent\Collection<Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<Event> $events
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * Maximum allowed months for report generation (absolute limit).
     */
    const ABSOLUTE_MAX_REPORT_MONTHS = 12;

    /**
     * Default maximum months for report generation.
     */
    const DEFAULT_MAX_REPORT_MONTHS = 3;

    /**
     * Default threshold for async report generation.
     */
    const DEFAULT_ASYNC_THRESHOLD_MONTHS = 6;

    /**
     * Name of the welcome team for new users.
     */
    const WELCOME_TEAM_NAME = 'Bienvenida';


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
        'max_report_months' => 'integer',
        'async_report_threshold_months' => 'integer',
        'max_member_teams' => 'integer',
        'force_max_workday_duration' => 'boolean',
        'max_workday_duration_minutes' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'timezone',
        'force_clock_in_delay',
        'clock_in_delay_minutes',
        'clock_in_grace_period_minutes',
        'special_event_color',
        'pdf_engine',
        'max_report_months',
        'async_report_threshold_months',
        'event_retention_months',
        'max_member_teams',
        'force_max_workday_duration',
        'max_workday_duration_minutes',
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

    /**
     * Check if this is the welcome team.
     *
     * @return boolean
     */
    public function isWelcomeTeam(): bool
    {
        return $this->name === self::WELCOME_TEAM_NAME;
    }

    /**
     * Scope a query to only include user-created teams (exclude Welcome team).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserCreated($query)
    {
        return $query->where('name', '!=', self::WELCOME_TEAM_NAME);
    }

    /**
     * Check if this team can be deleted.
     * Welcome team and teams with the personal_team flag cannot be deleted.
     *
     * @return boolean
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete the Welcome team
        if ($this->isWelcomeTeam()) {
            return false;
        }

        return true;
    }

    /**
     * Migrate a user's events to this team.
     * Used when transferring users between teams.
     *
     * @param \App\Models\User $user
     * @return int Number of events migrated
     */
    public function migrateUserEvents(User $user): int
    {
        return $user->events()->update(['team_id' => $this->id]);
    }
}

