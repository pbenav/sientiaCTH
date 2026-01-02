<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Holiday Model
 * 
 * Represents a holiday (national, regional, or local) for a specific team,
 * used for work schedule adjustments and time calculations.
 *
 * @property int $id
 * @property int $team_id
 * @property string $name Holiday name
 * @property string $date Holiday date (Y-m-d)
 * @property string $type Holiday type (national/regional/local)
 * @property string|null $observations
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Team $team
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class Holiday extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'date',
        'type',
        'team_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the team that owns the holiday.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
