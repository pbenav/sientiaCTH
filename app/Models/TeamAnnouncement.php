<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * TeamAnnouncement Model
 * 
 * Represents a team-wide announcement/notification with optional
 * date ranges for display scheduling and priority support.
 *
 * @property int $id
 * @property int $team_id
 * @property int $created_by_id
 * @property string $title
 * @property string $content
 * @property string $type Announcement type (info/warning/success/error)
 * @property bool $is_priority
 * @property bool $is_active
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Team $team
 * @property-read User $createdBy
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class TeamAnnouncement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'format',
        'is_active',
        'start_date',
        'end_date',
        'created_by',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'team_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the team that owns the announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who created the announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active announcements.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', Carbon::today());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', Carbon::today());
            });
    }

    /**
     * Check if the announcement is currently valid based on dates.
     *
     * @return bool
     */
    public function isCurrentlyValid()
    {
        $today = Carbon::today();
        
        $startValid = !$this->start_date || $this->start_date->lte($today);
        $endValid = !$this->end_date || $this->end_date->gte($today);
        
        return $this->is_active && $startValid && $endValid;
    }
}
