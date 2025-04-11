<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livraisons;
use App\Models\Clients;
use App\Models\Stock;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
class LivraisonController extends Controller
{
    //

    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            //return redirect()->route('dashboard_team_member');

            $teamMember = Auth::user();
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

            
            $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
            /*  if (!$team) {
                abort(404, "Vous n'etes membre d'aucune equipe");
            } */

            
            //$intBus = Business::find(optional($team->pivot)->business_id);
            
            $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way

            
            $clientOwner = User::findOrFail($realTeamMember->user_id);

            $businesses = $teamBusinessOwner->business()->paginate(10);
            $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();
            $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();
            view()->share('realTeamMember', $realTeamMember);


            $role = Role::find($realTeamMember->role_id);
            $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

            $role = Role::find($realTeamMember->role_id);
            view()->share('role', $formattedRole);
            view()->share('roleObj', $role);

            $livraisons = DB::table('livraisons')
            ->join('commandes' , 'livraisons.commande_id' , '=' , 'commandes.id')
            ->join('users' , 'commandes.user_id' , '=', 'users.id')
            ->where('users.id' , $teamBusinessOwner->id)
            ->select('livraisons.*')
            ->paginate(10);
            

            // Start the query to fetch clients
            $query = Livraisons::where('user_id', $teamBusinessOwner->id);
        
            // Apply the 'search' filter if provided
            if ($search = request('search')) {
                $query->where('name', 'like', "%" . $search . "%");
            }
        
            // Apply the 'email' filter if provided
            if ($email = request('email')) {
                $query->where('email', 'like', "%" . $email . "%");
            }
            $clients = Clients::where('user_id' ,$teamBusinessOwner->id)->get();

        
            // Apply the 'tel' (telephone) filter if provided
            if ($tel = request('tel')) {
                $query->where('tel', 'like', "%" . $tel . "%");
            }
        
            // Get the filtered clients and paginate the results
            //$livraisons = $query->paginate(10);
            $stocks = Stock::where('user_id' ,$teamBusinessOwner->id)->get();

            $user = User::where('email' , $realTeamMember->email)->first();

            return view('dashboard_team_member.livraisons.index', compact('livraisons', 'user', 'hasPhysique', 
            'hasPrestation', "businesses", 'realTeamMember' , 'clients' , 'stocks'));
        }
    
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
    
        // Start the query to fetch clients
        $query = Livraisons::where('user_id', $user->id);
    
        // Apply the 'search' filter if provided
        if ($search = request('search')) {
            $query->where('name', 'like', "%" . $search . "%");
        }
    
        // Apply the 'email' filter if provided
        if ($email = request('email')) {
            $query->where('email', 'like', "%" . $email . "%");
        }
        $clients = Clients::where('user_id' ,$user->id)->get();

    
        // Apply the 'tel' (telephone) filter if provided
        if ($tel = request('tel')) {
            $query->where('tel', 'like', "%" . $tel . "%");
        }
    
        // Get the filtered clients and paginate the results
        //$livraisons = $query->paginate(10);

        $livraisons = DB::table('livraisons')
            ->join('commandes' , 'livraisons.commande_id' , '=' , 'commandes.id')
            ->join('users' , 'commandes.user_id' , '=', 'users.id')
            ->where('users.id' , $user->id)
            ->select('livraisons.*')
            ->paginate(10);
        $stocks = Stock::where('user_id' ,$user->id)->get();

        return view('users.livraisons.index', compact('livraisons', 'hasPhysique', 
            'hasPrestation', "businesses", 'user' , 'clients' , 'stocks'));
    }
}
