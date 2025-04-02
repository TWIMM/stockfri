<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;


use Illuminate\Validation\ValidationException;


class TeamMemberController extends Controller
{

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }


    public function returnConfirmTeamMemberPwd($id){

        $teamM = TeamMember::findOrFail($id);
        $isUserPasswordConfirmed = User::find($teamM->id);
        if(!$isUserPasswordConfirmed || !$isUserPasswordConfirmed->id){
            return view('auth.create_team_member_password' , compact('teamM'));

        }

        return redirect()->route('login');

    }

    public function updatePasswordTeamMember(Request $request , $id){

        $validated = $request->validate([
            'password' => 'required|string|confirmed|min:8',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $teamMember = TeamMember::findOrFail($id);
          // Hash and update password
        //$teamMember->password = Hash::make($validated['password']);
        //$updateStatus = $teamMember->save(); // Use save() instead of update()
        $user = User::create([
            'name' => $teamMember->name,
            'email' => $teamMember->email,
            'type' => 'team_member',
            'password' => Hash::make($validated['password']),
        ]);

        if ($user) {
            \Log::info('Membre ajouté avec succès', ['team_member' => $user]);
            return redirect()->route('login')->with('success', 'Membre edited avec succès.');
        } else {
            \Log::error('Échec de l\'ajout du membre');
            return back()->with('error', 'Échec de la modification du membre');
        }

    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Redirect if the user is a team member
        if ($user->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

        // Retrieve businesses owned by the authenticated user
        $businessIds = Business::where('user_id', $user->id)->withTrashed()->pluck('id');

        // Retrieve teams associated with those businesses
        $teams = Team::whereHas('business', function ($query) use ($businessIds) {
            $query->whereIn('business_team.business_id', $businessIds);
        })->paginate(10);

        // Query to retrieve team members, applying the search filter if available
        $teamMembersQuery = TeamMember::with('team')->where('user_id', $user->id);

        // Apply search filter if 'search' query parameter is set
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $teamMembersQuery->where('name', 'like', '%' . $search . '%');
        }

        // Paginate the results
        $teamMembers = $teamMembersQuery->paginate(10);

        // Get business types for view
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        // Return the view with the filtered results
        return view('users.team_member.index', compact(
            'teamMembers', 
            'roles', 
            'permissions', 
            'teams', 
            'hasPhysique', 
            'hasPrestation', 
            'user'
        ));
    }




    public function showOwnerTeamMemberListPage()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');

        }

        $teamMember = Auth::user();

        $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

        
        $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
        /* if (!$team) {
            abort(404, "Vous n'etes membre d'aucune equipe");
        }
        $intBus = Business::find(optional($team->pivot)->business_id); */
        
        $user = User::findOrFail($realTeamMember->user_id);  

        // Retrieve all businesses the user owns (including soft deleted ones)
        $businessIds = Business::where('user_id', $user->id)->withTrashed()->pluck('id');

        // Retrieve all teams linked to those businesses
        $teams = Team::whereHas('business', function ($query) use ($businessIds) {
            $query->whereIn('business_team.business_id', $businessIds);
        })->paginate(10);

        // Retrieve team members even if their business was deleted
        $teamIds = $teams->pluck('id');
        $teamMembers = TeamMember::with('team')->where('user_id', $user->id)->paginate(10);
        view()->share('teamMembers', $teamMembers);
        view()->share('realTeamMember', $realTeamMember);

       // view()->share('realTeamMemberPermissions', $realTeamMemberPermissions);

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


        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('dashboard_team_member.team_member.owner_index', compact(
            'teamMembers', 
            'roles', 
            'permissions', 
            'teams', 
            'hasPhysique', 
            'hasPrestation', 
            'user'
        ));
    }



    public function getTeams($id): JsonResponse
    {
        $teamMember = TeamMember::with('team')->find($id);

        if (!$teamMember) {
            return response()->json(['error' => 'Team member not found'], 404);
        }

        $teamsWithPermissions = $teamMember->team->map(function ($team) {
            $business = Business::find(optional($team->pivot)->business_id);
            $businessName = $business ? $business->name : 'All';
            return [
                'id' => $team->id,
                'name' => $team->name,
                'business_id' => $businessName, 

                'permissions' => json_decode(optional($team->pivot)->permissions ?? '[]', true), 
            ];
        });

        return response()->json([
            'teams' => $teamsWithPermissions
        ]);
    }


    public function getPermissions(Request $request): JsonResponse
    {
        // Retrieve 'ids' from the route parameter
        $ids = explode(',', $request->route('ids')); // Split by commas if there are multiple ids
        //dd($ids);
    
        if (empty($ids)) {
            return response()->json(['permissions' => []]); // Always return 'permissions' key
        }
    
        // Fetch all permissions at once
        $permissions = Permission::whereIn('id', $ids)->get(['id', 'name']);
        $permissionsAll = Permission::all();

        return response()->json([
            'permissions' => $permissions, 
            'permissionsAll'=> $permissionsAll, 
        ]);
    }
    
    
    

    public function create()
    {
        $teams = Team::all();
        return view('users.team_member.create', compact('teams'));
    }

   

    public function store(Request $request)
    {
        \Log::info('Store request received', $request->all());

        // Validate request data
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:team_members,name',
            'email' => 'required|string|email|max:255|unique:team_members,email',
            'tel' => 'required|string',
            'team_member' => 'required|string',
        ]);

        // Determine user_id based on the team_member field
        $user_id = null;
        $owner_user_id = null;

        if ($request->team_member === 'nothere') {
            $user_id = Auth::id(); // Assign the authenticated user ID
        } elseif ($request->team_member === 'there') {
            $teamMember = Auth::user();
            $realTeamMember = TeamMember::where('email', $teamMember->email)->first();

            if (!$realTeamMember) {
                return back()->with('error', 'Team Member not found.');
            }

            $team = $realTeamMember->team()->first();
            if (!$team) {
                return back()->with('error', 'Team association not found.');
            }

            $businessId = optional($team->pivot)->business_id;
            $business = Business::find($businessId);

            if (!$business) {
                return back()->with('error', 'Associated business not found.');
            }

            $user_id = $business->user_id;
        } else {
            return back()->with('error', 'Invalid team_member value.');
        }

        // Ensure user_id is set
        if (!$user_id) {
            return back()->with('error', 'Failed to determine user ownership.');
        }

        \Log::info('Validated data', ['validated' => $validated, 'user_id' => $user_id]);

        // Create the TeamMember with user_id included
        $teamMemberData = array_merge($validated, ['user_id' => $user_id]);
        $teamM = TeamMember::create($teamMemberData);

        $success = $this->emailService->sendEmailWithTemplate($teamM->email, 'emails.teammate_confirm' , [
            'name' => $teamM->name,      
            'appLink' => env("APP_URL").env('SIGN_IN_TEAM_MATE_LINK')."/".$teamM->id,   
        ]);


        if ($teamM) {
            \Log::info('Team Member successfully added', ['team_member' => $teamM]);
            return back()->with('success', 'Membre ajouté avec succès.');
        } else {
            \Log::error('Failed to add team member');
            return back()->with('error', 'Échec de l\'ajout du membre.');
        }
    }



    public function edit(TeamMember $teamMember)
    {
        $teams = Team::all();
        return view('users.team_member.edit', compact('teamMember', 'teams'));
    }

    


    public function getTeamMemberById($id){
        $teamM = TeamMember::find($id);
        return response()->json($teamM);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|string|email',
            'tel' => 'required',
        ]);

        $teamMember = TeamMember::findOrFail($id);
        $teamMemberSaved = $teamMember->update($validated);

        if ($teamMemberSaved) {
            \Log::info('Membre modifie avec succès', ['team_member' => $teamMemberSaved]);
            return redirect()->route('team_member.listes')->with('success', 'Membre edited avec succès.');
        } else {
            \Log::error('Échec de l\'ajout du membre');
            return back()->with('error', 'Échec de la modification du membre');
        }

    }


    public function del($id)
    {
        $teamMember = TeamMember::findOrFail($id);

        $teamMember->delete();
        return redirect()->route('team_member.listes')->with('success', 'Membre supprimé avec succès.');
    }

    public function RemoveFrom(Request $request)
    {


        $teamMemberId = $request->query('team_member_id');
        $teamId = $request->query('team_id');

        // Validate that both IDs are provided
        if (!$teamMemberId || !$teamId) {
            return redirect()->back()->with('error', 'Invalid request: Missing parameters.');
        }
        
        $pivotData = DB::table('team_member_team')
            ->where('team_id', $teamId)
            ->where('team_member_id', $teamMemberId)
            ->delete();

        //$pivotData->delete();
        
      
        return redirect()->route('team_member.listes')->with('success', 'Membre supprimé avec succès.');
    }


    public function assignMember(Request $request)
    {
        // Validate the request
        $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'team_member_id' => 'required|exists:team_members,id',
            'business_id' => 'nullable|exists:business,id',
            'team_member' => 'required|string',

            'mode_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array', // Ensure permissions is an array
            'permissions.*' => 'exists:permissions,id' // Each permission must exist
        ]);

        if ($request->team_member === 'nothere') {
            $user_id = Auth::id(); // Assign the authenticated user ID
        } elseif ($request->team_member === 'there') {
            $teamMember = Auth::user();
            $realTeamMember = TeamMember::where('email', $teamMember->email)->first();

            if (!$realTeamMember) {
                return back()->with('error', 'Team Member not found.');
            }

            $user_id = $realTeamMember->user_id;

        } else {
            return back()->with('error', 'Invalid team_member value.');
        }

        if ($request->mode_admin) {
            $team = Team::where('name', 'admin')->first();
            if(!$team){
                $team = Team::create([
                    'name' => 'admin',
                    'user_id' => $user_id,
        
                    
                ]);
            }
        } else {
            $team = Team::find($request->team_id);
        }

        // Find the team and team member
        $teamMember = TeamMember::find($request->team_member_id);
    
        if (!$team || !$teamMember) {
            return redirect()->back()->with('error', 'Team or Team Member not found.');
        }

        $pivotData = DB::table('team_member_team')
            //->where('team_id', $team->id)
            ->where('team_member_id', $request->team_member_id)
            ->get();
        
      

        if($request->mode_admin){
            $pivotDataToDelete = DB::table('team_member_team')
            ->where('team_member_id', $request->team_member_id)
            ->delete();

        }
    
        
        foreach ($pivotData as $key) {
            // Must Not be admin 
            if($key->mode_admin == 1){
                return redirect()->back()->with('error', 'Team Member already admin.');
            }
            
        }


        $pivotData = DB::table('team_member_team')
            ->where('team_member_id', $request->team_member_id)
            ->get();
        
        foreach ($pivotData as $key) {
          
            // Must Not be in another team Linked to the same business (better edit and add up permissions)
            if( $key->business_id == $request->business_id){
                return redirect()->back()->with('error', 'Team Member already in a team for the same business.');
            }
            
        }
        

        //dd($pivotData); 

        // If mode_admin is selected, set business_id to null and hide business-specific permissions
        $businessId = $request->mode_admin ? null : $request->business_id;
        if ($request->mode_admin) {
            $permissions = Permission::all()->pluck('id')->toArray();
        } else {
            $permissions = $request->permissions ?? []; // Use selected permissions if not mode_admin
        }
    
        // Store permissions as JSON
        $team->members()->attach($teamMember->id, [
            'permissions' => json_encode($permissions ?? []), 
            'business_id' => $businessId,
            'mode_admin' =>  $request->mode_admin ? true : false

        ]);

        

            return $request->mode_admin 
            ? redirect()->back()->with('success', 'Team Member successfully granted admin.') 
            : redirect()->back()->with('success', 'Team Member successfully added to the team.');
        }



    


}
