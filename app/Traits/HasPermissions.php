<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\UserPermission;
use App\Services\PermissionService;
use Illuminate\Support\Facades\App;

trait HasPermissions
{
    /**
     * Check if user has a permission.
     */
    public function hasPermission(string $permission, array $context = []): bool
    {
        return App::make(PermissionService::class)->checkPermission($this, $permission, $context);
    }

    /**
     * Grant a permission to the user.
     */
    public function givePermissionTo(Permission|string $permission, array $context = [])
    {
        App::make(PermissionService::class)->grantPermission($this, $permission, auth()->user(), $context);
    }

    /**
     * Revoke a permission from the user.
     */
    public function revokePermissionTo(Permission|string $permission)
    {
        App::make(PermissionService::class)->revokePermission($this, $permission, auth()->user());
    }

    /**
     * Relationship: Direct user permissions.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
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
