<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Traits\TimeDiff;
use App\Traits\InsertHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a time tracking event, such as a clock-in or clock-out.
 *
 * This model contains all the information related to a single work event,
 * including start and end times, the associated user, and its status.
 */
class Event extends Model
{
    use HasFactory;
    use TimeDiff;
    use InsertHistory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
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
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->timeDiff($this->start, $this->end, true);
    }

    /**
     * Mark the event as confirmed (closed).
     *
     * @return void
     */
    public function confirm()
    {
        error_log('Confirm...');
        if ($this->is_open === true) {
            $this->is_open = false;
        }
        $this->save();
    }

    /**
     * Toggle the confirmation status of the event.
     *
     * @return void
     */
    public function toggleConfirm()
    {
        $orig_ev = clone $this;        
        $this->is_open = !$this->is_open;
        $this->save();
        if (auth()->user()->isTeamAdmin()) {
            $this->insertHistory('events', $orig_ev, $this);
        }
        unset($orig_ev);
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
     * Scope a query to only include open events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsOpen(Builder $query)
    {
        return $query->where('is_open', '=', 1);
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
