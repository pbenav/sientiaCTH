<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start',
        'end',
        'is_open',
        'description'
    ];

    protected $options = [
        'join' => ', ',
        'parts' => 2,
        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPeriod()
    {
        //Set the start date
        $start_date = Carbon::parse($this->start);
        $end_date = Carbon::parse($this->end);
        //Set the end date
        if ($this->end != null) {
            //Count the difference in Hours and minutes
            return $end_date->diffForHumans($start_date, $this->options);
        } else {
            return $this->start;
        }
    }
    public function confirm()
    {
        if ($this->is_open == 1) {
            $this->is_open = 0;
            $this->save();
        }
    }

    public function scopeUserId(Builder $query, $scope)
    {
        return $query->where('user_id', $scope);

    }

    public function scopeMonth(Builder $query, $scope)
    {
        return $query->whereMonth('start', $scope);

    }

    public function scopeDay(Builder $query, $scope)
    {
        return $query->whereDay('start', $scope);

    }

    public function scopeDescription(Builder $query, $scope)
    {
        return $query->where('description', $scope);

    }
   
    /**
     * Scope a query to only include events that are in open state
     *
     * @param $query
     */
    public function scopeIsOpen(Builder $query)
    {
        return $query->where('is_open', '=', 1);
    }

    public function scopeEventsPerUserMonth(Builder $query, $user_id, $month, $description)
    {
        return $query->selectRaw('user_id as user_id, DAY(start) as day,
                                    MONTH(start) as month,
                                    SUM(TIMESTAMPDIFF(minute, start, end))/60 as hours,
                                    description as description')
            ->where('user_id', $user_id)
            ->where('description', 'like', '%' . $description . '%')
            ->whereMonth('start', $month)
            ->groupBy('user_id', 'start', 'description')
            ->get();
    }


    public function scopefilterEvents(Builder $query, $start, $end, $user_id, $is_open)
    {
        return $query->selectRaw('id, user_id, start, end, is_open')
            ->where('start', '>=', Carbon::parse($start))
            ->where('end', '<=', Carbon::parse($end))
            ->where('user_id', $user_id)
            ->where('is_open', $is_open)
            ->orderBy('start', 'asc')
            ->get();
    }

    public function getEventsPerUser($teamusers, $search, $confirmed, $sort, $direction, $qtytoshow)
    {
        return $this::select(
            'events.id',
            'events.user_id',
            'users.name',
            'users.family_name1',
            'events.start',
            'events.end',
            'events.description',
            'events.is_open'
        )
            ->join('users', 'user_id', '=', 'users.id')
            ->whereIn('user_id', $teamusers)
            ->where(function ($query) use ($search, $confirmed) {
                $query->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('events.user_id', $search)
                    ->orWhere('users.family_name1', 'like', '%' . $search . '%')
                    ->orWhere('users.family_name2', 'like', '%' . $search . '%')
                    ->orWhere('events.description', 'like', '%' . $search . '%');
            })
            ->where(function ($query) use ($confirmed) {
                if ($confirmed) {
                    $query->where('events.is_open', '=', '1');
                }
            })
            ->orderBy($sort, $direction)
            ->Paginate($qtytoshow);
    }

    public function getEventsFiltered($teamusers, $filtered, Event $filter, $sort, $direction, $qtytoshow)
    {
        return $this::select(
            'events.id',
            'events.user_id',
            'users.name',
            'users.family_name1',
            'events.start',
            'events.end',
            'events.description',
            'events.is_open'
        )
            ->join('users', 'user_id', '=', 'users.id')
            ->whereIn('events.user_id', $teamusers)
            ->where(function ($query) use ($filtered, $filter) {
                if ($filtered) {
                    error_log('Filtering...');
                    if (!is_null($filter->start)) {
                        error_log('By name...');
                        $query->whereDate('events.start', '>=', $filter->start);
                    }
                    if (!is_null($filter->start)) {
                        error_log('By start date...');
                        $query->whereDate('events.end', '<=', $filter->end);
                    }
                    if (!empty($filter->name)) {
                        error_log('By end date...');
                        $query->where('users.name', 'like', $filter->name);
                    }
                    if (!empty($filter->family_name1)) {
                        error_log('By family name...');
                        $query->where('users.family_name1', $filter->family_name1);
                    }
                    if ($filter->is_open) {
                        error_log('By status...');
                        $query->where('events.is_open', '1');
                    }
                    if ($filter->description != __('All')) {
                        error_log('By description...');
                        $query->where('events.description', $filter->description);
                    }
                }
            })
            ->orderBy($sort, $direction)
            ->paginate($qtytoshow)->withQueryString();
    }
}