<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\Stock;
use App\Models\Pay;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Commandes;
use App\Models\Magasins;
use App\Models\Livraisons;
use App\Models\Services;

class ClientController extends Controller
{
    public function showStat (){
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
    
        // Start the query to fetch clients
        $query = Clients::where('user_id', $user->id);
    
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
    
        // Get the filtered clients and paginate the results
        $clients = $query->paginate(10);
        $commandeTotalPerClient = function($purchases) {
            $countCommandeTotalPerClient = 0;
            foreach($purchases as $purchase){
                $countCommandeTotalPerClient += $purchase->total_price;
            }
            return number_format($countCommandeTotalPerClient);
        };
        $clientList = Clients::where('user_id', $user->id)->get();
        return view('users.statistiques.clients_state', compact('clients' , 'commandeTotalPerClient' , 'clientList', 'hasPhysique', 
            'hasPrestation', "businesses", 'user'));
    }


    public function getStat($client){


        if(auth()->user()->type === 'client'){
            if (!Auth::user()->pricing_id) {
                return redirect()->route('pricing.page');
            }
            
            if (!session()->has('active_tab')) {
                session(['active_tab' => 'service']);
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
            $approvedSelledProduct = Commandes::whereHas('commandeItems', function ($query) {
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

            $serviceApprovedPaginated = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('client_id' , $client)
            ->where('validation_status' , 'approved')
            ->paginate(10);

            $stockApprovedPaginated = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('client_id' , $client)
            ->where('validation_status' , 'approved')
            ->paginate(10);

            $clientIdid =  $client;
            $countApprovedSelledServices = count($commandeApproved);

            $query  = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id', $user->id)
            ->where('validation_status', 'approved');
    
            // Apply filters based on form input
            if ($clientId = request('client')) {
                $query->where('client_id', $clientId);  // Filter by client ID
            }
    
            if ($status = request('status')) {
                if ($status !== 'none') {  // If the status is provided (not 'none'), apply it
                    $query->where('delivery_status', $status);  // Filter by delivery status
                }
            }
    
            if ($minPrice = request('min_price')) {
                $query->where('total_price', '>=', $minPrice);  // Filter by minimum price
            }
    
            if ($maxPrice = request('max_price')) {
                $query->where('total_price', '<=', $maxPrice);  // Filter by maximum price
            }
    
            if ($dateStart = request('date_start')) {
                $query->where('created_at', '>=', $dateStart);  // Filter by start date
            }
    
            if ($dateEnd = request('date_end')) {
                $query->where('created_at', '<=', $dateEnd);  // Filter by end date
            }
    
            // Execute the query and paginate the results //SERVICES
            $commandeNotApproved = $query->paginate(10);


            $querypROD  = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id', $user->id)
            ->where('validation_status', 'approved');
    
            // Apply filters based on form input
            if ($clientId = request('client')) {
                $querypROD->where('client_id', $clientId);  // Filter by client ID
            }
    
            if ($status = request('status')) {
                if ($status !== 'none') {  // If the status is provided (not 'none'), apply it
                    $querypROD->where('delivery_status', $status);  // Filter by delivery status
                }
            }
    
            if ($minPrice = request('min_price')) {
                $querypROD->where('total_price', '>=', $minPrice);  // Filter by minimum price
            }
    
            if ($maxPrice = request('max_price')) {
                $querypROD->where('total_price', '<=', $maxPrice);  // Filter by maximum price
            }
    
            if ($dateStart = request('date_start')) {
                $querypROD->where('created_at', '>=', $dateStart);  // Filter by start date
            }
    
            if ($dateEnd = request('date_end')) {
                $querypROD->where('created_at', '<=', $dateEnd);  // Filter by end date
            }
    
            // Execute the query and paginate the results //SERVICES
            $approvedSelledProduct = $querypROD->paginate(10);
           

             // Get additional data
            $clients = Clients::where('user_id', $user->id)->get();
            $magasins = Magasins::where('user_id', $user->id)->paginate(10);
            //$stocks = Stock::where('user_id', $user->id)->paginate(10);
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
            $livraisons = $query->paginate(10);
            $stocks = Stock::where('user_id' ,$user->id)->get();
            //$countLivraions = count($livraisonsForCount);
            $livraisonsForCount = DB::table('livraisons')
            ->join('commandes' , 'livraisons.commande_id' , '=' , 'commandes.id')
            ->join('clients' , 'commandes.client_id' , '=', 'clients.id')
            ->where('clients.id' , $client)
            ->select('livraisons.*')
            ->get();
            $countLivraions = count($livraisonsForCount);



            $livraisons = DB::table('livraisons')
            ->join('commandes' , 'livraisons.commande_id' , '=' , 'commandes.id')
            ->join('clients' , 'commandes.client_id' , '=', 'clients.id')
            ->where('clients.id' , $client)
            ->select('livraisons.*')
            ->paginate(10);
            //session(['active_tab' => 'service']);
            $client = Clients::find($client);
            return view('users.statistiques.client_detail', compact('businesses' , 'stockApprovedPaginated',  'serviceApprovedPaginated' , 'categories' , 'livraisons', 'countLivraions',  'getClientFromId', 'fournisseurs', 
            'commandeNotApproved' , 'magasins' , 'stocks','client', 'clients' , 'clientIdid', 'hasPhysique', 'countApprovedSelledServices',  
            'countApprovedSelledProduct' , 'approvedSelledProduct' ,'countBusiness', 'hasPrestation' , 'countTeamMembers' , 
             'countTeams', 'countClients', 'user'));
        } else if(auth()->user()->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

    }


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
        
            // Get the filtered clients and paginate the results
            $clients = $query->paginate(10);
            $user = User::where('email' , $realTeamMember->email)->first();
            return view('dashboard_team_member.clients.index', compact('clients' , 'user', 'hasPhysique', 
            'hasPrestation', "businesses", 'realTeamMember'));
        }
    
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
    
        // Start the query to fetch clients
        $query = Clients::where('user_id', $user->id);
    
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
    
        // Get the filtered clients and paginate the results
        $clients = $query->paginate(10);
    
        return view('users.clients.index', compact('clients', 'hasPhysique', 
            'hasPrestation', "businesses", 'user'));
    }
    

    public function handleDebt( Request $request){
        $request->validate([
            'id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            //'file' => 'mimes:pdf,jpeg,jpg,png|max:2048', 
            'factures_remboursement' => 'required|array', 
            'factures_remboursement.*' => 'mimes:pdf,jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('factures_remboursement')) {
            foreach ($request->file('factures_remboursement') as $file) {
                // Store each file in the 'factures_add_up_quantity' directory inside 'public' disk
                $filePath = $file->store('factures_remboursements', 'public');
                $filePaths[] = $filePath; // Save the file path in the array for later use
            }
        }


        $client = Clients::find($request->id);
        $limitCredit =  $client->current_debt + $client->limit_credit_for_this_user; 

        if($request->amount > $client->current_debt){
            $client->current_debt = 0; 
        } else {
            $client->current_debt = $client->current_debt - $request->amount; 

        }
        $client->limit_credit_for_this_user = $limitCredit;
        $client->save();
        
        return redirect()->back()->with('success' , 'Credit rembourse avec succes');
    }


    public function getDette(Request $request)
    {
        // Check user type, if 'team_member' redirect to the team member dashboard
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

            // Start building the query for clients
            $clientsQuery = Clients::where('user_id', $teamBusinessOwner->id)
                ->where('current_debt', '>', 0); // Always filter for clients with debt

            // Apply search filter if a 'search' query is provided
            if ($request->has('search') && !empty($request->search)) {
                $clientsQuery->where('name', 'like', '%' . $request->search . '%');  // Searching by client name
            }

            // Apply limit filter if a 'limite' query is provided
            if ($request->has('limite') && !empty($request->limite)) {
                $clientsQuery->where('limit_credit_for_this_user', '<=', $request->limite);  // Filter by credit limit
            }

            // Apply current debt filter if a 'dette_actuelle' query is provided
            if ($request->has('dette_actuelle') && !empty($request->dette_actuelle)) {
                $clientsQuery->where('current_debt', '<=', $request->dette_actuelle);  // Filter by current debt
            }

            // Paginate the filtered clients
            $clients = $clientsQuery->paginate(10);

            // Fetch stock data (if needed)
            $stocks = Stock::where('user_id', $teamBusinessOwner->id)->paginate(10);
            $user = User::where('email' , $realTeamMember->email)->first();

            
            return view('dashboard_team_member.finances.creence_clients', compact(
                'clients'   , 'user', 'stocks', 'hasPhysique', 'hasPrestation', 'businesses', 'realTeamMember'
            ));
        }

        // Get the authenticated user
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business;

        // Start building the query for clients
        $clientsQuery = Clients::where('user_id', $user->id)
            ->where('current_debt', '>', 0); // Always filter for clients with debt

        // Apply search filter if a 'search' query is provided
        if ($request->has('search') && !empty($request->search)) {
            $clientsQuery->where('name', 'like', '%' . $request->search . '%');  // Searching by client name
        }

        // Apply limit filter if a 'limite' query is provided
        if ($request->has('limite') && !empty($request->limite)) {
            $clientsQuery->where('limit_credit_for_this_user', '<=', $request->limite);  // Filter by credit limit
        }

        // Apply current debt filter if a 'dette_actuelle' query is provided
        if ($request->has('dette_actuelle') && !empty($request->dette_actuelle)) {
            $clientsQuery->where('current_debt', '<=', $request->dette_actuelle);  // Filter by current debt
        }

        // Paginate the filtered clients
        $clients = $clientsQuery->paginate(10);

        // Fetch stock data (if needed)
        $stocks = Stock::where('user_id', $user->id)->paginate(10);

        // Return the view with the filtered results
        return view('users.finances.creence_clients', compact(
            'clients', 'stocks', 'hasPhysique', 'hasPrestation', 'businesses', 'user'
        ));
    }

    
    
    public function getPays()
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

            $clients = Clients::where('user_id' ,$teamBusinessOwner->id)->get();

            $paiements = Pay::where('user_id' ,$teamBusinessOwner->id)
            //->where('current_debt' , '>' , 0)
            //->where('limit_credit_for_this_user' , '<=' , 0)
            ->paginate(10);
            $stocks = Stock::where('user_id' ,$teamBusinessOwner->id)->paginate(10); 
            $user = User::where('email' , $realTeamMember->email)->first();

            return view('dashboard_team_member.finances.paiements', compact('paiements' , 'user', 'clients' , 'stocks','hasPhysique', 
                'hasPrestation', "businesses",  'realTeamMember'));
        }
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $clients = Clients::where('user_id' ,$user->id)->get();

        $paiements = Pay::where('user_id' ,$user->id)
        //->where('current_debt' , '>' , 0)
        //->where('limit_credit_for_this_user' , '<=' , 0)
        ->paginate(10);
        $stocks = Stock::where('user_id' ,$user->id)->paginate(10); 

        return view('users.finances.paiements', compact('paiements' , 'clients' , 'stocks','hasPhysique', 
        'hasPrestation', "businesses",  'user'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function edit( $id)
    {
        $client = Clients::find($id);
        return response()->json($client);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'tel' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

       

        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

        if($isAuthTeamMemberQuestionMark->type === 'client'){
            Clients::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'email' => $request->email,
                'tel' => $request->tel,
                'address' => $request->address,
            ]);
        }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
            
            $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
            Clients::create([
                'user_id' => $test->user_id,
                'name' => $request->name,
                'email' => $request->email,
                'tel' => $request->tel,
                'address' => $request->address,
            ]);
        }

        return redirect()->back()->with('success', 'Success');
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $id,
            'tel' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);
        $client = Clients::find($id);


        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'tel' => $request->tel,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Success');
    }

    public function destroy($id)
    {
        $client = Clients::find($id);

        $client->delete();

        return redirect()->back()->with('success', 'Success');
    }
}
