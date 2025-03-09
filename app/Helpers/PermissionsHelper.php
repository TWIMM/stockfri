<?php

use Illuminate\Support\Facades\Auth;
use App\Models\TeamMember;

if (!function_exists('userHasPermission')) {
    /**
     * Check if the authenticated user has a specific permission.
     *
     * @param string|array $permissions Single permission or an array of permissions.
     * @return bool
     */
    function userHasPermission($permissions)
    {
        $user = Auth::user();

        $realTeamMember = TeamMember::where('email', $teamMember->email)->first();

        
        if (!$user) {
            return false;
        }

        // If single permission is given, check it
        if (is_string($permissions)) {
            return $user->hasPermission($permissions);
        }

        // If array of permissions, check if user has at least one
        return collect($permissions)->some(fn($permission) => $user->hasPermission($permission));
    }
}
