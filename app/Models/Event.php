<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        };
    }

    public function scopeEventsPerUser(Builder $query, $user_id, $month){
        return $query->selectRaw('ANY_VALUE(user_id) as user_id, DAY(start) as day,
                                    ANY_VALUE(MONTH(start)) as month,
                                    ANY_VALUE(SUM(TIMESTAMPDIFF(minute, start, end))/60) as hours')
        ->where('user_id', $user_id)
        ->whereMonth('start', $month)
        ->groupByRaw(DB::raw('DAY(start)'))          
        ->get()
        ->pluck('hours', 'day'); 
    }
    
    /**
     * Scope a query to only include events that are in open state
     *
     * @param $query
     */
    public function scopeIsOpen($query)
    {
       return $query->where('is_open', '=', 1)->get();
    }

    public function scopefilterEvents(Builder $query, $start, $end, $user_id, $is_open )
    {        
        return $query->selectRaw('id, user_id, start, end, is_open')
        ->where('start', '>=', Carbon::parse($start))
        ->where('end', '<=', Carbon::parse($end))
        ->where('user_id', $user_id)
        ->where('is_open', $is_open)
        ->orderBy('start', 'asc')
        ->get();
    }
}