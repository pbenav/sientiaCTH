<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use PhpParser\Node\Expr\Cast\String_;

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

    public function get_period()
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
}
