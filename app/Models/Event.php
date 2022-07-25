<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
