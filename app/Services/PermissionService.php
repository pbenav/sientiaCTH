<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\PermissionAuditLog;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Check if a user has a specific permission.
     */
    public function checkPermission(User $user, string $permissionName, array $context = []): bool
    {
        // 1. Check Cache
        $cacheKey = $this->getCacheKey($user, $permissionName, $context);
        
        // Use verifyPermission logic inside cache remember
        $allowed = Cache::remember($cacheKey, 60, function () use ($user, $permissionName, $context) {
             return $this->verifyPermission($user, $permissionName, $context);
        });

        // 2. Audit (optional to audit checks to avoid spam, maybe only audit failed ones or critical ones)
        // For now, let's look at requirements. GESTOR_DE_PERMISOS says "Audita uso del permiso".
        // We can audit asynchronously or conditionally.
        $this->auditUsage($user, $permissionName, 'checked', $allowed ? 'allowed' : 'denied', $context);

        return $allowed;
    }

    /**
     * Core verification logic (without cache/audit).
     * HYBRID SYSTEM: Checks granular permissions + legacy role system
     */
    protected function verifyPermission(User $user, string $permissionName, array $context): bool
    {
        // 0. Admin global override - ALWAYS has all permissions
        if ($user->is_admin) {
            return true;
        }

        $teamId = $context['team_id'] ?? ($user->currentTeam?->id ?? null);
        if ($teamId instanceof Team) $teamId = $teamId->id;

        // 1. Check Direct User Permissions (granular system)
        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            // Permission doesn't exist in new system, try legacy fallback
            // Esto permite retrocompatibilidad con código que use permisos no definidos
            return $this->checkLegacyPermission($user, $permissionName, $teamId);
        }

        $hasDirect = $user->permissions()
            ->where('permission_id', $permission->id)
            ->where(function ($query) use ($teamId) {
                $query->whereNull('team_id')
                      ->orWhere('team_id', $teamId);
            })
            ->where(function ($query) {
                // Check validity dates
                $now = now();
                $query->where(function ($q) use ($now) {
                    $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                })->where(function ($q) use ($now) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
                });
            })
            ->whereNull('revoked_at') // Only active permissions
            ->exists();

        if ($hasDirect) return true;

        // 2. Check Custom Roles (via TeamUser custom_role_id)
        if ($teamId) {
            // Get user's role in the team
            $teamUser = DB::table('team_user')
                ->where('user_id', $user->id)
                ->where('team_id', $teamId)
                ->first();

            if ($teamUser && $teamUser->custom_role_id) {
                $role = Role::find($teamUser->custom_role_id);
                if ($role) {
                     $hasRolePermission = $role->permissions()->where('name', $permissionName)->exists();
                     if ($hasRolePermission) return true;
                }
            }
        }
        
        // 3. Fallback to Legacy Role System (owner/admin/user)
        // NOTA: Este fallback se mantiene durante la fase de transición a producción.
        // Una vez verificado el correcto funcionamiento en producción (1-2 semanas),
        // se puede eliminar este fallback y migrar todo el código al nuevo sistema.
        return $this->checkLegacyPermission($user, $permissionName, $teamId);
    }

    /**
     * Check permissions using legacy role system (owner/admin/user).
     * 
     * SISTEMA LEGACY - FALLBACK TEMPORAL
     * Este método se mantiene como red de seguridad durante la transición a producción.
     * Permite que el código antiguo siga funcionando mientras se migra gradualmente.
     * 
     * TODO: Eliminar este método una vez:
     *   1. El sistema esté estable en producción (1-2 semanas)
     *   2. Todo el código legacy esté migrado al nuevo sistema
     *   3. Se hayan actualizado todas las referencias a hasTeamRole(), is_admin checks, etc.
     */
    protected function checkLegacyPermission(User $user, string $permissionName, ?int $teamId): bool
    {
        if (!$teamId) {
            return false;
        }

        $team = Team::find($teamId);
        if (!$team) {
            return false;
        }

        // Get legacy role from team_user
        $teamUser = DB::table('team_user')
            ->where('user_id', $user->id)
            ->where('team_id', $teamId)
            ->first();

        if (!$teamUser) {
            return false;
        }

        $legacyRole = $teamUser->role;

        // Map permission names to legacy roles
        // Owner has everything
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Admin has most things except transfer ownership
        if ($legacyRole === 'admin') {
            $adminDenied = ['teams.transfer_ownership', 'teams.delete'];
            return !in_array($permissionName, $adminDenied);
        }

        // Member has limited permissions
        if ($legacyRole === 'member') {
            $memberAllowed = [
                'events.view.own', 'events.view.team', 'events.create.own',
                'events.update.own', 'events.delete.own', 'events.export',
                'teams.view', 'teams.view_settings', 'teams.switch',
                'event_types.view', 'holidays.view', 'work_centers.view',
                'announcements.view', 'reports.view', 'reports.generate',
                'users.view', 'roles.view', 'permissions.view'
            ];
            return in_array($permissionName, $memberAllowed);
        }

        // Inspector has read-only
        if ($legacyRole === 'inspect') {
            return str_contains($permissionName, '.view');
        }

        // Editor has user permissions + announcement management
        if ($legacyRole === 'editor') {
            $editorAllowed = [
                'events.view.own', 'events.view.team', 'events.create.own',
                'events.update.own', 'events.delete.own', 'events.export',
                'teams.view', 'teams.view_settings', 'teams.switch',
                'event_types.view', 'holidays.view', 'work_centers.view',
                'announcements.view', 'announcements.create', 'announcements.update',
                'announcements.delete', 'announcements.publish',
                'reports.view', 'reports.generate',
                'users.view', 'roles.view', 'permissions.view'
            ];
            return in_array($permissionName, $editorAllowed);
        }

        return false;
    }

    public function grantPermission(User $user, Permission|string $permission, ?User $grantedBy = null, array $context = []): void
    {
        $permissionObj = $permission instanceof Permission ? $permission : Permission::where('name', $permission)->firstOrFail();
        
        $teamId = $context['team_id'] ?? null;
        $validFrom = $context['valid_from'] ?? null;
        $validUntil = $context['valid_until'] ?? null;
        $extraContext = $context['context'] ?? null;

        $user->permissions()->attach($permissionObj->id, [
            'team_id' => $teamId,
            'context' => $extraContext ? json_encode($extraContext) : null,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'granted_by' => $grantedBy?->id,
            'granted_at' => now(),
        ]);

        $this->clearCache($user);
        $this->auditUsage($user, $permissionObj->name, 'granted', 'allowed', $context, $grantedBy);
    }

    public function revokePermission(User $user, Permission|string $permission, ?User $revokedBy = null): void
    {
        $permissionObj = $permission instanceof Permission ? $permission : Permission::where('name', $permission)->firstOrFail();

        // Needs logic to match specific context if user has multiple same permissions?
        // For simplicity, revoke all instances or require specific ID. 
        // Let's detach by permission_id for now.
        
        // Better: update revoked_at instead of detach for history?
        // Requirements say "revokePermission", schema has revoked_by/at in user_permissions.
        // But belongsToMany detach removes the row. 
        // We should PROBABLY soft-delete or update row. 
        // But `user_permissions` table doesn't have soft deletes, it has `revoked_at`.
        // So we update the pivot? But `belongsToMany` is tricky with updating pivot for logical delete.
        // Let's use `UserPermission` model directly to update.
        
        $userPermissions = UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permissionObj->id)
            ->whereNull('revoked_at')
            ->get();

        foreach($userPermissions as $up) {
            $up->update([
                'revoked_by' => $revokedBy?->id,
                'revoked_at' => now(),
            ]);
        }

        $this->clearCache($user);
        $this->auditUsage($user, $permissionObj->name, 'revoked', 'allowed', [], $revokedBy);
    }

    protected function auditUsage(User $user, string $permissionName, string $action, string $result, array $context = [], ?User $performedBy = null)
    {
        PermissionAuditLog::create([
            'user_id' => $user->id,
            'permission_name' => $permissionName,
            'action' => $action,
            'result' => $result,
            'performed_by' => $performedBy?->id,
            'team_id' => $context['team_id'] ?? null,
            'context' => json_encode($context),
            'ip_address' => request()->ip(), // optional helper
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function getCacheKey(User $user, string $permission, array $context): string
    {
        $teamId = $context['team_id'] ?? 'global';
        return "permissions:{$user->id}:{$teamId}:{$permission}";
    }

    public function clearCache(User $user) 
    {
        // Wildcard clearing is hard with standard cache drivers.
        // We might use tags if available (Redis), or just short TTL.
        // For now, doing nothing or simple tagging if supported.
        if (Cache::supportsTags()) {
            Cache::tags(['permissions', "user:{$user->id}"])->flush();
        }
    }
}
