<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission Model
 * 
 * Represents a system permission that can be assigned to roles or users.
 * Permissions control access to features and actions within the application.
 *
 * @property int $id
 * @property string $name Permission identifier (e.g., 'events.create')
 * @property string $display_name Human-readable name
 * @property string|null $description
 * @property string|null $category Permission category for grouping
 * @property bool $requires_context Requires team/context
 * @property bool $is_system System permission (cannot be deleted)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<Role> $roles
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
        'requires_context',
        'is_system',
    ];

    protected $casts = [
        'requires_context' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('granted_by', 'granted_at');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot([
                'team_id',
                'context',
                'valid_from',
                'valid_until',
                'granted_by',
                'granted_at',
                'revoked_by',
                'revoked_at'
            ]);
    }
}
