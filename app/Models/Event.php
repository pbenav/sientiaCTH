<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PhpParser\Node\Expr\Cast\String_;

class Event extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'start',
        'end',
        'is_open',
        'description'];

        public function user()
        {
            return $this->belongsTo(Users::class);
        }  

    public function get_period(){
        //Set the start date
        $start_date = Carbon::createFromFormat('Y-m-d H:i:s', $this->start);
        //Set the end date
        if($this->end != null){
            $end_date = Carbon::createFromFormat('Y-m-d H:i:s', $this->end);
        } else {
            $end_date = $this->start;
        }
     //Count the difference in Hours     
     return $start_date->diffInHours($end_date);
    }

    public function confirm(){
        if ($this->is_open == 1) {
            $this->is_open = 0;
            $this->save();
        };
    }

    
}
