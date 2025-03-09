<?php

namespace App\Http\Controllers;
use App\Models\TeamMember;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the user dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        if(auth()->user()->type === 'client'){
            if (!Auth::user()->pricing_id) {
                return redirect()->route('pricing.page');
            }

            $user = Auth::user();

            $businesses = $user->business()->paginate(10);

            $hasPhysique = $user->business()->where('type', 'business_physique')->exists();

            $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();


            return view('welcome', compact('businesses', 'hasPhysique', 'hasPrestation' , 'user'));
        } else if(auth()->user()->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

        
    }


    public function team_member()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');

        }

        $teamMember = Auth::user();
        $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

        
        $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
       /*  if (!$team) {
            abort(404, "Vous n'etes membre d'aucune equipe");
        } */

        
        //$intBus = Business::find(optional($team->pivot)->business_id);
        
        $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

        $businesses = $teamBusinessOwner->business()->paginate(10);
        $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();
        view()->share('realTeamMember', $realTeamMember);


        $role = Role::find($realTeamMember->role_id);
        $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

        $role = Role::find($realTeamMember->role_id);
        view()->share('role', $formattedRole);
        view()->share('roleObj', $role);

        return view('welcome_team_member', compact('businesses', 'hasPhysique', 'hasPrestation' , 'realTeamMember'));
    }

}
