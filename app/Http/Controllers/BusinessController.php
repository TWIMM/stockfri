<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\TeamMember;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\Team;


class BusinessController extends Controller
{
    
    
    public function showBusinessListPage(Request $request)
    {
        $user = Auth::user();

        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
        }

        // Check if a search term is provided
        $query = $user->business();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $businesses = $query->paginate(10);

        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();

        return view('users/business_list', compact('businesses', 'hasPhysique', 'hasPrestation', 'user'));
    }


    public function getBusinessesByTeam($teamId): JsonResponse
    {
        $team = Team::with('business')->find($teamId);

        if (!$team) {
            return response()->json(['message' => 'Équipe non trouvée'], 404);
        }

        return response()->json([
            'businesses' => $team->business // Assuming `business` is the relationship
        ]);
    }



    public function showOwnerBusinessListPage()
    {
        $teamMember = Auth::user();
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');

        }
        $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

       // $canViewBusiness = $realTeamMember->hasPermission('view business');


        $team = $realTeamMember->team()->first();   // ✅ Correct way to access the relationship
        //$intBus = Business::find(optional($team->pivot)->business_id);
        
        $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

        $businesses = $teamBusinessOwner->business()->paginate(10);

        $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();

        $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();


        view()->share('realTeamMember', $realTeamMember);
       // view()->share('canViewBusiness', $canViewBusiness);


        $role = Role::find($realTeamMember->role_id);
        $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

        $role = Role::find($realTeamMember->role_id);
        view()->share('role', $formattedRole);
        view()->share('roleObj', $role);

        return view('dashboard_team_member/owner_business_list', compact('businesses', 'hasPhysique', 'hasPrestation'));
    }


    // Fetch business details
    public function getBusiness($id)
    {
        $business = Business::find($id);
        return response()->json($business);
    }

    public function del($id)
    {
        // Find the business by ID
        $business = Business::findOrFail($id);
        
        // Delete the business
        $business->delete();
        
        // Redirect back with a success message
        return redirect()->route('business.listes')->with('success', 'Business deleted successfully.');
    }


    public function updateBusiness(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            //'type' => 'required|string',
            'description' => 'required|string',
            'ifu' => 'required|string|max:255',
            'commercial_number' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'number' => 'required|string',
        ]);

        // Find the business to update
        $business = Business::findOrFail($id);

        // Update the business
        $savedbusiness = $business->update([
            'name' => $request->input('name'),
            'ifu' => $request->input('ifu'),
            'description' => $request->input('description'),
            //'type' => $request->input('type'),
            'commercial_number' => $request->input('commercial_number'),
            'business_email' => $request->input('business_email'),
            'number' => $request->input('number'),
        ]);


        if ($savedbusiness) {
            \Log::info('Membre ajouté avec succès', ['savedbusiness' => $savedbusiness]);
            return redirect()->route('business.listes')->with('success', 'Business created successfully.');
        } else {
            \Log::error('Échec de l\'ajout du membre');
            return back()->with('error', 'Échec de l\'ajout du business');
        }

    }

    



    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'ifu' => 'required|string|max:255',
            'commercial_number' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'business_email' => 'required|email',
        ]);
        $user_id = 0;

        if( !$request->team_member){
            $user_id = Auth::id();
        } else {
            $teamMember = Auth::user();
          
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);
    
            $team = $realTeamMember->team()->first();  
            //$intBus = Business::find(optional($team->pivot)->business_id);
            $user = User::findOrFail($realTeamMember->user_id);
           // $teamBusiness = $team->business()->first(); 
           // $teamBusinessOwner = $teamBusiness->user; 
            $user_id = $user->id;
        }


        $exist = Business::where('user_id', $user_id)
        ->where('name', $request->name)
        ->exists();

        if($exist){
            return back()->with('error', 'Le nom est deja associe a un de vos business');
        }

        // Create the business
        $business = new Business([
            'user_id' => $user_id,
            'name' => $request->name,
            'description' => $request->description,
            'ifu' => $request->ifu,
            'type' => $request->type,
            'commercial_number' => $request->commercial_number,
            'number' => $request->number,
            'business_email' => $request->business_email,
        ]);

        // Save the business
        $savedbusiness = $business->save();

        if ($savedbusiness) {
            \Log::info('Membre ajouté avec succès', ['savedbusiness' => $savedbusiness]);
            if( !$request->team_member){
                return redirect()->route('business.listes')->with('success', 'Business created successfully.');

            } else {
                return redirect()->route('owner.business.listes')->with('success', 'Business created successfully.');

            }
        } else {
            \Log::error('Échec de l\'ajout du membre');
            return back()->with('error', 'Échec de l\'ajout du business');
        }

        // Redirect with success message
    }
}
