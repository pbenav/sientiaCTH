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
}
