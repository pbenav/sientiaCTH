<?php

namespace App\Models;

use App\Traits\TimeDiff;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;
    use TimeDiff;

    protected $fillable = [
        'user_id',
        'start',
        'end',
        'is_open',
        'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPeriod()
    {
        return $this->timeDiff($this->start, $this->end, true);
    }

    public function confirm()
    {
        error_log('Confirm...');
        if ($this->is_open == 1) {
            $this->is_open = 0;
        }
        $this->save();
    }

    public function toggleConfirm()
    {
        error_log('Toggle confirm...');
        $this->is_open = !$this->is_open;
        $this->save();
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

    public function scopeIsOpen(Builder $query)
    {
        return $query->where('is_open', '=', 1);
    }

    public function scopeEventsPerUserMonth(Builder $query, $user_id, $month, $year, $description)
    {
        return $query->selectRaw('user_id as user_id, DAY(start) as day,
                                    MONTH(start) as month,
                                    SUM(TIMESTAMPDIFF(minute, start, end))/60 as hours,
                                    description as description')
            ->where('user_id', $user_id)
            ->where('description', 'like', '%' . $description . '%')
            ->whereMonth('start', $month)
            ->whereYear('start', $year)
            ->groupBy('user_id', 'start', 'description')
            ->get();
    }


    public function scopefilterEvents(Builder $query, $start, $end, $user_id, $is_open)
    {
        return $query->select('id, user_id, start, end, is_open')
            ->where('start', '>=', Carbon::parse($start))
            ->where('end', '<=', Carbon::parse($end))
            ->where('user_id', $user_id)
            ->where('is_open', $is_open)
            ->orderBy('start', 'asc')
            ->get();
    }

    public function getEventsFiltered($teamusers, Event $filter, $sort, $direction, $qtytoshow)
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
            ->when(!is_null($filter->start), fn ($query) => $query->whereDate('events.start', '>=', $filter->start))
            ->when(!is_null($filter->end), fn ($query) => $query->whereDate('events.end', '<=', $filter->end))
            ->when(!empty($filter->name), fn ($query) => $query->where('users.name', $filter->name))
            ->when(!empty($filter->family_name1), fn ($query) => $query->where('users.family_name1', $filter->family_name1))
            ->when($filter->is_open, fn ($query) => $query->where('events.is_open', '1'))
            ->when($filter->description != __('All'), fn ($query) => $query->where('events.description', $filter->description))
            ->orderBy($sort, $direction)
            ->paginate($qtytoshow);
    }

    public function getEventsPerUser($teamusers, $confirmed, $search, $sort, $direction, $qtytoshow)
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
            ->where(function ($query) use ($search) {
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
}
