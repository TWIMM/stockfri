<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Business;
use App\Models\Commandes;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use App\Models\MouvementDeStocks;
use App\Models\Clients;

class FournisseurController extends Controller
{
    public function showStat (Request $request){
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
            
            
            // Start the query to fetch clients
            $query = Clients::where('user_id', $teamBusinessOwner->id);
        
            // Apply the 'search' filter if provided
            if ($search = request('search')) {
                $query->where('name', 'like', "%" . $search . "%");
            }
        
            // Apply the 'email' filter if provided
            if ($email = request('email')) {
                $query->where('email', 'like', "%" . $email . "%");
            }
        
            // Apply the 'tel' (telephone) filter if provided
            if ($tel = request('tel')) {
                $query->where('tel', 'like', "%" . $tel . "%");
            }

           /*  $approvedSelledProduct = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('client_id' , $client)

            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledProduct = count($approvedSelledProduct);
            $commandeApproved = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('client_id' , $client)
            ->where('validation_status' , 'approved')
            ->get();
            $countApprovedSelledServices = count($commandeApproved);

            $commandCount = $countApprovedSelledProduct + $countApprovedSelledServices; */
        
            // Get the filtered clients and paginate the results
            $clients = $query->paginate(10);
            $user = User::where('email' , $realTeamMember->email)->first();
            return view('dashboard_team_member.clients.index', compact('clients' , 'commandCount', 'user', 'hasPhysique', 
            'hasPrestation', "businesses", 'realTeamMember'));
        }
    
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
    
        // Build query to filter suppliers
        $query = Fournisseur::where('user_id', $user->id);

        // Filter by 'search' if provided
        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by 'email' if provided
        if ($email = request('email')) {
            $query->where('email', 'like', "%{$email}%");
        }

        // Filter by 'phone' if provided
        if ($phone = request('phone')) {
            $query->where('phone', 'like', "%{$phone}%");
        }

        // Filter by 'address' if provided
        if ($address = request('address')) {
            $query->where('address', 'like', "%{$address}%");
        }

        // Get the filtered results with pagination
        $fournisseurs = $query->paginate(10);
    
        
        $commandeTotalPerFournisseur = function($fournisseurId , $what_to_return) 
        {
            $countCommandeTotalPerFournisseur = 0;
            $countCommandeTotalMoneyPerFournisseur = 0;
            //$countCommandeTotalBuyerBonus = 0;

            $achatChezFournisseur = MouvementDeStocks::whereNotNull('fournisseur_id')
            ->where('fournisseur_id', $fournisseurId)
            ->where('user_id', auth()->id())
            ->where('type_de_mouvement', 'achat_de_stock_chez_fournisseur')
            ->get();

            //$countCommandeTotalPerFournisseur = count($achatChezFournisseur);

            foreach ($achatChezFournisseur as $value) {
                $countCommandeTotalMoneyPerFournisseur +=  $value->prix_fournisseur * $value->quantity;
                $countCommandeTotalPerFournisseur +=  $value->quantity;

            }


            if($what_to_return === 'quantity'){
                return number_format($countCommandeTotalPerFournisseur);

            } else if($what_to_return === 'price'){
                return number_format($countCommandeTotalMoneyPerFournisseur);

            } else {
                //return number_format($countCommandeTotalBuyerBonus);

            }

        };
        
        return view('users.statistiques.fournisseur', compact('fournisseurs' , 'commandeTotalPerFournisseur' , 'hasPhysique', 
            'hasPrestation', "businesses", 'user'));
    }


    public function getStat($fournisseur){


        if(auth()->user()->type === 'client'){
            if (!Auth::user()->pricing_id) {
                return redirect()->route('pricing.page');
            }
            
            if (!session()->has('active_tab_fournisseur_stat')) {
                session(['active_tab_fournisseur_stat' => 'fournisseur']);
            }

            $user = Auth::user();

            $businesses = $user->business()->paginate(10);

            $hasPhysique = $user->business()->where('type', 'business_physique')->exists();

            $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();

            $categories = $user->categorieProduits;
            $fournisseurs = $user->fournisseurs;

            // Get the client by ID (for later use in views)
            $getClientFromId = function($id) {
                return Clients::find($id);
            };

            $countClients = count(Clients::where('user_id' ,$user->id)->get());
            $countTeams = count(Team::where('user_id' ,$user->id)->get());
            $countTeamMembers = count(TeamMember::where('user_id' ,$user->id)->get());
            $countBusiness = count(Business::where('user_id' ,$user->id)->get());
            

           
            $mouvementDeStocksForCount = DB::table('mouvement_de_stocks')
            ->where('type_de_mouvement' , 'achat_de_stock_chez_fournisseur')
            ->where('fournisseur_id' , $fournisseur)
            ->where('user_id' , auth()->id())
            ->get();

            //dd($mouvementDeStocksForCount);

            $mouvementDeStocksForPagination = DB::table('mouvement_de_stocks')
            ->where('type_de_mouvement' , 'achat_de_stock_chez_fournisseur')
            ->where('fournisseur_id' , $fournisseur)
            ->where('user_id' , auth()->id())
            ->paginate(10);
            $countMouvementDeStocksForCount = count($mouvementDeStocksForCount);
            $fournisseur = Fournisseur::find($fournisseur);


            session(['active_tab_fournisseur_stat' => 'fournisseur']);
            return view('users.statistiques.fournisseur_detail', compact('businesses' , 'categories' ,   'getClientFromId', 'fournisseurs', 
        'mouvementDeStocksForPagination' ,'fournisseur' ,  'hasPhysique',  
              'countBusiness', 'hasPrestation' , 'countTeamMembers' , 
             'countTeams', 'countClients', 'user'));
        } else if(auth()->user()->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

    }


    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business;

        // Build query to filter suppliers
        $query = Fournisseur::where('user_id', $user->id);

        // Filter by 'search' if provided
        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by 'email' if provided
        if ($email = request('email')) {
            $query->where('email', 'like', "%{$email}%");
        }

        // Filter by 'phone' if provided
        if ($phone = request('phone')) {
            $query->where('phone', 'like', "%{$phone}%");
        }

        // Filter by 'address' if provided
        if ($address = request('address')) {
            $query->where('address', 'like', "%{$address}%");
        }

        // Get the filtered results with pagination
        $fournisseurs = $query->paginate(10);

        return view('users.fournisseurs.index', compact('fournisseurs', 'hasPhysique', 'hasPrestation', 'businesses', 'user'));
    }

    public function indexTeamMember()
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

        $query = Fournisseur::where('user_id', $teamBusinessOwner->id);

        // Filter by 'search' if provided
        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by 'email' if provided
        if ($email = request('email')) {
            $query->where('email', 'like', "%{$email}%");
        }

        // Filter by 'phone' if provided
        if ($phone = request('phone')) {
            $query->where('phone', 'like', "%{$phone}%");
        }

        // Filter by 'address' if provided
        if ($address = request('address')) {
            $query->where('address', 'like', "%{$address}%");
        }

        // Get the filtered results with pagination
        $fournisseurs = $query->paginate(10);


        
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
        $user = User::where('email' , $realTeamMember->email)->first();


        return view('dashboard_team_member.fournisseurs.index', compact('fournisseurs', 'user', 'hasPhysique', 'hasPrestation', 'businesses', 'realTeamMember'));

    }


    // Afficher le formulaire de création
    public function create()
    {
        return view('fournisseurs.create');
    }

    // Ajouter un fournisseur
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            

        ]);

        $isAuthTeamMemberQuestionMark = Auth::id();
        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

        if($isAuthTeamMemberQuestionMark->type === 'client'){
            Fournisseur::create([
                'name' => $request->name,
                'email' => $request->email ?? '',
                'phone' => $request->phone ?? '',
                'address' => $request->address ?? '',
                'ifu' => $request->ifu ? $request->ifu : null,
                'user_id' => Auth::id(), 
            ]);
        }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {

            $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
            //dd(Auth::id());
            //dd( $test->user_id);
            Fournisseur::create([
                'name' => $request->name,
                'email' => $request->email ?? '',
                'phone' => $request->phone ?? '',
                'address' => $request->address ?? '',
                'ifu' => $request->ifu ? $request->ifu : null,
                'user_id' => $test->user_id, 
            ]);
        }

        

        return redirect()->back()->with('success', 'Fournisseur ajouté avec succès!');
    }

    // Afficher le formulaire de modification
    public function edit($id)
    {
        $fournisseur = Fournisseur::find($id);
        return response()->json([
            'fournisseur'       => $fournisseur,
        ]);

    }

    // Mettre à jour un fournisseur
    public function update($id , Request $request)
    {
        

        $request->validate([
            'name' => 'required|string|max:255',
           
        ]);

        $fournisseur = Fournisseur::find($id);

        $fournisseur->update($request->all());

        return redirect()->back()->with('success', 'Fournisseur mis à jour avec succès!');
    }

    // Supprimer un fournisseur
    public function destroy($id)
    {
        $fournisseur = Fournisseur::find($id);

        $fournisseur->delete();

        return  redirect()->back()->with('success', 'Fournisseur supprimé avec succès!');
    }
}
