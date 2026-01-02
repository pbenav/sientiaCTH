<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Traits\TimeDiff;
use App\Traits\InsertHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Event Model
 * 
 * Represents a time tracking event (clock-in/clock-out) with support for
 * geolocation, NFC tags, work centers, and event type classification.
 *
 * @property int $id
 * @property int $user_id
 * @property int $team_id
 * @property int|null $work_center_id
 * @property int|null $event_type_id
 * @property int|null $authorized_by_id
 * @property \Carbon\Carbon $start Event start datetime
 * @property \Carbon\Carbon|null $end Event end datetime (null if open)
 * @property bool $is_open Event is currently open (not closed)
 * @property bool $is_authorized Event has been authorized
 * @property bool $is_closed_automatically Closed automatically by system
 * @property bool $is_extra_hours Marked as extra hours
 * @property bool $is_exceptional Exceptional event
 * @property string|null $description Event description
 * @property string|null $observations Additional observations
 * @property float|null $latitude GPS latitude coordinate
 * @property float|null $longitude GPS longitude coordinate
 * @property string|null $location_start Location name at start
 * @property string|null $location_end Location name at end
 * @property string|null $nfc_tag_id NFC tag identifier
 * @property string|null $ip_address IP address of request
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read User $user
 * @property-read Team $team
 * @property-read WorkCenter|null $workCenter
 * @property-read EventType|null $eventType
 * @property-read User|null $authorizedBy
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class Event extends Model
{
    use HasFactory;
    use TimeDiff;
    use InsertHistory;
    use \App\Traits\HandlesTimezoneConversion;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'work_center_id',
        'start',
        'end',
        'is_open',
        'is_authorized',
        'is_closed_automatically',
        'is_extra_hours',
        'is_exceptional',
        'description',
        'observations',
        'event_type_id',
        'latitude',
        'longitude',
        'location_start',
        'location_end',
        'nfc_tag_id',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_open' => 'boolean',
        'is_extra_hours' => 'boolean',
        'is_exceptional' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'location_start' => 'array',
        'location_end' => 'array',
    ];

    /**
     * Get the event type associated with the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * Get the user who owns the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team associated with the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the work center where the event took place.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    /**
     * Get the user who authorized the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by_id');
    }

    /**
     * Calculate the duration of the event.
     * NOTA: Siempre devuelve días naturales (para listados).
     *
     * @return string
     */
    public function getPeriod()
    {
        if ($this->eventType && $this->eventType->is_all_day) {
            // IMPORTANTE: Convertir de UTC a zona horaria local antes de calcular
            $timezone = $this->getEventTimezone($this);
            $days = $this->calculateAllDayEventDays($this->start, $this->end, $timezone);
            
            return $days . ' ' . ($days == 1 ? __('day') : __('days'));
        }

        return $this->timeDiff($this->start, $this->end, true);
    }

    /**
     * Calculate the duration of the event respecting user preferences.
     * Para eventos autorizables, respeta vacation_calculation_type del usuario.
     *
     * @param \App\Models\User|null $user Usuario para verificar preferencias
     * @return string
     */
    public function getPeriodForUser($user = null)
    {
        if ($this->eventType && $this->eventType->is_all_day) {
            $timezone = $this->getEventTimezone($this);
            
            // Si no hay usuario o no es evento autorizable, usar días naturales
            if (!$user || !$this->eventType->is_authorizable) {
                $days = $this->calculateAllDayEventDays($this->start, $this->end, $timezone);
                return $days . ' ' . ($days == 1 ? __('day') : __('days'));
            }
            
            // Verificar preferencia del usuario
            if ($user->vacation_calculation_type === 'working') {
                // Calcular días hábiles
                $start = $this->utcToTeamTimezone($this->start, $timezone);
                $end = $this->utcToTeamTimezone($this->end, $timezone);
                
                $startDay = $start->copy()->startOfDay();
                $endDay = $end->copy()->startOfDay();
                
                if ($end->format('H:i:s') !== '00:00:00') {
                    $endDay->addDay();
                }
                
                // Obtener festivos del equipo
                $team = $user->currentTeam;
                $holidays = [];
                if ($team) {
                    $holidays = $team->holidays()
                        ->whereBetween('date', [$startDay, $endDay])
                        ->pluck('date')
                        ->map(fn($date) => $date->format('Y-m-d'))
                        ->toArray();
                }
                
                // Contar días hábiles
                $workingDays = 0;
                $current = $startDay->copy();
                while ($current->lt($endDay)) {
                    $dayOfWeek = (int) $current->format('N');
                    $dateString = $current->format('Y-m-d');
                    
                    if ($dayOfWeek < 6 && !in_array($dateString, $holidays)) {
                        $workingDays++;
                    }
                    
                    $current->addDay();
                }
                
                $days = $workingDays < 1 ? 1 : $workingDays;
                return $days . ' ' . ($days == 1 ? __('day') : __('days'));
            }
            
            // Días naturales
            $days = $this->calculateAllDayEventDays($this->start, $this->end, $timezone);
            return $days . ' ' . ($days == 1 ? __('day') : __('days'));
        }

        return $this->timeDiff($this->start, $this->end, true);
    }

    /**
     * Mark the event as confirmed (closed).
     *
     * @return bool Returns true if the event was confirmed, false otherwise.
     */
    public function confirm(): bool
    {
        if (!$this->hasCompleteDates()) {
            return false;
        }
        
        error_log('Confirm...');
        if ($this->is_open === true) {
            $this->is_open = false;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Toggle the confirmation status of the event.
     *
     * @return bool Returns true if the event status was toggled, false otherwise.
     */
    public function toggleConfirm(): bool
    {
        // If trying to close an event, check if it has complete dates
        if ($this->is_open && !$this->hasCompleteDates()) {
            return false;
        }
        
        $orig_ev = clone $this;        
        $this->is_open = !$this->is_open;
        $this->save();
        if (auth()->user()->isTeamAdmin()) {
            // Solo audita si el evento quedó cerrado (is_open = false)
            $this->insertHistory('events', $orig_ev, $this, false);
        }
        unset($orig_ev);
        return true;
    }

    /**
     * Check if the event has both start and end dates/times.
     *
     * @return bool
     */
    public function hasCompleteDates(): bool
    {
        return !is_null($this->start) && !is_null($this->end);
    }

    /**
     * Scope a query to only include events for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserId(Builder $query, $scope)
    {
        return $query->where('user_id', $scope);
    }

    /**
     * Scope a query to only include events in a specific month.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMonth(Builder $query, $scope)
    {
        return $query->whereMonth('start', $scope);
    }

    /**
     * Scope a query to only include events on a specific day.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDay(Builder $query, $scope)
    {
        return $query->whereDay('start', $scope);
    }

    /**
     * Scope a query to only include events with a specific description.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDescription(Builder $query, $scope)
    {
        return $query->where('description', $scope);
    }

    /**
     * Scope: Get only open events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsOpen(Builder $query)
    {
        return $query->where('is_open', '=', 1);
    }

    /**
     * Scope: Get events with eager loaded relationships.
     * Optimizes N+1 queries for common use cases.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(['user', 'team', 'eventType', 'workCenter', 'authorizedBy']);
    }

    /**
     * Scope: Get events for a specific date range.
     * Uses indexed columns for optimal performance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Carbon\Carbon|string  $startDate
     * @param  \Carbon\Carbon|string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        
        return $query->where('start', '<=', $end)
                     ->where('end', '>=', $start);
    }

    /**
     * Scope: Get events for a specific team.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $teamId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope: Get events for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get closed events only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('is_open', false);
    }

    /**
     * Get a summary of events per user for a given month and year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $user_id
     * @param  int  $month
     * @param  int  $year
     * @param  string  $description
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function scopeEventsPerUserMonth(Builder $query, $user_id, $month, $year, $description)
    {
        return $query->selectRaw('user_id as user_id, DAY(start) as day,
                                    MONTH(start) as month,
                                    SUM(TIMESTAMPDIFF(minute, start, end))/60 as hours,
                                    description')
            ->where('user_id', $user_id)
            ->where('description', 'like', '%' . $description . '%')
            ->whereMonth('start', $month)
            ->whereYear('start', $year)
            ->groupBy('user_id', 'start', 'description')
            ->get();
    }

    /**
     * Filter events by a date range, user, and open status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $start
     * @param  string  $end
     * @param  int  $user_id
     * @param  bool  $is_open
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function scopefilterEvents(Builder $query, $start, $end, $user_id, $is_open)
    {
        return $query->select('id, user_id, start, end, is_open, observations')
            ->where('start', '>=', Carbon::parse($start))
            ->where('end', '<=', Carbon::parse($end))
            ->where('user_id', $user_id)
            ->where('is_open', $is_open)
            ->orderBy('start', 'asc')
            ->get();
    }
}
