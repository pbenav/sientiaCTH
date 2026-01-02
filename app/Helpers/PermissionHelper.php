<?php

if (!function_exists('userCan')) {
    /**
     * Check if the authenticated user has a permission.
     * 
     * @param string $permission
     * @param mixed $team
     * @return bool
     */
    function userCan(string $permission, $team = null): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->can($permission, $team);
    }
}

if (!function_exists('userCannot')) {
    /**
     * Check if the authenticated user does NOT have a permission.
     * 
     * @param string $permission
     * @param mixed $team
     * @return bool
     */
    function userCannot(string $permission, $team = null): bool
    {
        return !userCan($permission, $team);
    }
}

if (!function_exists('userHasAnyPermission')) {
    /**
     * Check if the authenticated user has ANY of the given permissions.
     * 
     * @param array $permissions
     * @param mixed $team
     * @return bool
     */
    function userHasAnyPermission(array $permissions, $team = null): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->hasAnyPermission($permissions, $team);
    }
}

if (!function_exists('userHasAllPermissions')) {
    /**
     * Check if the authenticated user has ALL of the given permissions.
     * 
     * @param array $permissions
     * @param mixed $team
     * @return bool
     */
    function userHasAllPermissions(array $permissions, $team = null): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->hasAllPermissions($permissions, $team);
    }
}
