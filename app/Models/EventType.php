<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * EventType Model
 * 
 * Represents a type of time tracking event (workday, vacation, sick leave, etc.)
 * with configurable properties for calendar display and business logic.
 *
 * @property int $id
 * @property int $team_id
 * @property string $name Event type name
 * @property string|null $description
 * @property string $color Hex color for calendar display
 * @property bool $is_all_day All-day event flag
 * @property bool $is_workday_type Counts as workday
 * @property bool $is_authorizable Requires authorization
 * @property bool $is_break_type Is a break/pause type
 * @property bool $is_pause_type Pauses work timer
 * @property string|null $observations
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Team $team
 * @property-read \Illuminate\Database\Eloquent\Collection<Event> $events
 * 
 * @version 1.0.0
 * @since 2025-01-10
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
        'is_authorizable',
        'is_pause_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_all_day' => 'boolean',
        'is_workday_type' => 'boolean',
        'is_authorizable' => 'boolean',
        'is_pause_type' => 'boolean',
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
