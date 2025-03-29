<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(Request $request)
    {

        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');

        }

        $user = Auth::user();

        // Get the business IDs of the user
        $businessIds = $user->business()->withTrashed()->pluck('id');

        $teamsQuery = Team::whereHas('business', function ($query) use ($businessIds) {
            $query->whereIn('business.id', $businessIds);
        });
    
        // Apply the filter if the search parameter is provided
        if ($request->has('search') && !empty($request->search)) {
            $teamsQuery->where('name', 'like', '%' . $request->search . '%'); // Example filter by team name
        }
    
        // Paginate the results
        $teams = $teamsQuery->paginate(10);

        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();

        $businesses = $user->business; 

        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();

        return view('users.teams.index', compact('teams' , 'businesses' ,  'hasPhysique', 'hasPrestation', 'user'));
    }


    public function showOwnerTeamListPage()
    {

        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');

        }

        $teamMember = Auth::user();
        $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

        $team = $realTeamMember->team->first();  // ✅ Correct way to access the relationship
        //$intBus = Business::find(optional($team->pivot)->business_id);
        
        $user = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

        
        // Get the business IDs of the user
        $businessIds = $user->business()->withTrashed()->pluck('id');

        // Retrieve teams that are associated with these businesses through the pivot table
        $teams = Team::whereHas('business', function ($query) use ($businessIds) {
            $query->whereIn('business.id', $businessIds);
        })->paginate(10);

        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();

        $businesses = $user->business; 

        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();

        view()->share('realTeamMember', $realTeamMember);


        $role = Role::find($realTeamMember->role_id);
        $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

        $role = Role::find($realTeamMember->role_id);
        view()->share('role', $formattedRole);
        view()->share('roleObj', $role);
        view()->share('user', $user);


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

        //$permissions = 
        view()->share('isUserAdminQuestionMark', $isUserAdminQuestionMark);


        return view('dashboard_team_member.teams.owner_index', compact('teams' , 'businesses' ,  'hasPhysique', 'hasPrestation'));
    }

    public function create()
    {
        $businesses = Business::all(); 
        return view('users.teams.create', compact('businesses'));
    }

    public function getTeamsById($id){
        $team = Team::with('business')->find($id);
    
        if (!$team) {
            return response()->json(['error' => 'Team not found'], 404);
        }
        
        return response()->json([
            'team'       => $team,
            'businesses' => $team->business,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_ids' => 'required|array', // Expecting an array of business IDs
            'business_ids.*' => 'exists:business,id', // Ensures each business ID exists
            'name' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);

        // Create the team
        $teamSaved = Team::create([
            'name' => $request->input('name'),
            'user_id' => $request->input('user_id'),

        ]);

        // Sync businesses with the team (associate multiple businesses with the team)
        $teamSaved->business()->sync($request->input('business_ids'));

        if ($teamSaved) {
            \Log::info('Team created successfully', ['teamSaved' => $teamSaved]);
            return redirect()->route('teams.listes')->with('success', 'Team created successfully.');
        } else {
            \Log::error('Team creation failed');
            return back()->with('error', 'Failed to create the team');
        }
    }


    public function show(Team $team)
    {
        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $businesses = Business::all();
        return view('teams.edit', compact('team', 'businesses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'business_ids' => 'required|array',
            'business_ids.*' => 'exists:business,id',
            'name_edit' => 'required',
        ]);

        // Find the team
        $team = Team::findOrFail($id);

        // Update the team name
        $teamSaved = $team->update([
            'name' => $request->input('name_edit'),
        ]);

        // Sync businesses with the team (update the pivot table)
        $team->business()->sync($request->input('business_ids'));

        if ($teamSaved) {
            \Log::info('Team updated successfully', ['teamSaved' => $teamSaved]);
            return redirect()->route('teams.listes')->with('success', 'Team updated successfully.');
        } else {
            \Log::error('Team update failed');
            return back()->with('error', 'Failed to update the team');
        }
    }


    public function del($id)
    {
        // Find the team by ID
        $team = Team::findOrFail($id);

        // Get all the team members for this team
        $teamMembers = $team->members;  // This is a collection of actual TeamMember models

        // Loop through each team member and delete their associated user
        foreach ($teamMembers as $teamMember) {
            TeamMember::deleteAssociatedUser($teamMember->email);  // Delete the user associated with the team member
        }

        // Finally, delete the team
        $team->delete();

        // Redirect with a success message
        return redirect()->route('teams.listes')->with('success', 'Team and associated team members removed successfully.');
    }

}
