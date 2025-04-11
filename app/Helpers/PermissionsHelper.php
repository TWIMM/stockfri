<?php

use Illuminate\Support\Facades\Auth;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\User;


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


if (!function_exists('isUserAdminQuestionMark')) {
    /**
     * Check if the authenticated user has a specific permission.
     *
     * @param string|array $permissions Single permission or an array of permissions.
     * @return bool
     */
    function isUserAdminQuestionMark()
    {
        $teamMember = Auth::user();

        $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

        $clientOwner = User::findOrFail($realTeamMember->user_id);

        $teamAdminMarked = Team::where('name', 'admin')->where('user_id', $clientOwner->id)->first();


        $isUserAdminQuestionMarkMode  = DB::table('team_member_team')
            ->where('team_id', $teamAdminMarked->id)
            ->where('team_member_id', $realTeamMember->id)
            ->where('mode_admin', 1)
            ->first();

        $isUserAdminQuestionMark = false; 

        if($isUserAdminQuestionMarkMode && $isUserAdminQuestionMarkMode->id){
            $isUserAdminQuestionMark = true ; 
        }

        return  $isUserAdminQuestionMark ;
    }
}
