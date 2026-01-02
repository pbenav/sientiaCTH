<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model
 * 
 * Represents a user role within a team with associated permissions.
 * Roles define sets of permissions that can be assigned to users.
 *
 * @property int $id
 * @property int|null $team_id Team context (null for global roles)
 * @property string $name Role identifier
 * @property string $display_name Human-readable name
 * @property string|null $description
 * @property bool $is_system System role (cannot be deleted)
 * @property int|null $created_by User ID who created the role
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Team|null $team
 * @property-read User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<Permission> $permissions
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'team_id',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot('granted_by', 'granted_at');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, TeamUser::class, 'custom_role_id', 'id', 'id', 'user_id');
    }
    
    // Helper to get direct users assigned via team_user
    public function teamUsers() {
         return $this->hasMany(TeamUser::class, 'custom_role_id');
    }
}
