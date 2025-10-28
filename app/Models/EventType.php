<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a type of event, such as "Work," "Vacation," or "Sick Leave."
 *
 * Event types are used to categorize events and can have different properties,
 * such as a specific color for display in the calendar.
 */
class EventType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'team_id',
        'color',
        'is_all_day',
        'is_workday_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_all_day' => 'boolean',
        'is_workday_type' => 'boolean',
    ];

    /**
     * Get the team that owns the event type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
