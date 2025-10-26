<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a work center or location.
 *
 * This model is used to store information about the different locations where
 * users can clock in and out.
 */
class WorkCenter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'code',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
    ];

    /**
     * Get the team that owns the work center.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
