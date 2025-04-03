<?php

namespace App\Http\Controllers;
use App\Models\TeamMember;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clients;
use App\Models\Commandes;
use App\Models\ClientDebt;
use App\Models\Magasins;
use App\Models\Stock;
use App\Models\Livraisons;
use App\Models\Services;

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
            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledProduct = count($approvedSelledProduct);
            $commandeApproved = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('validation_status' , 'approved')
            ->get(); 
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
            $countLivraions = count(Livraisons::where('user_id', $user->id)->get());


            return view('welcome', compact('businesses' , 'categories' , 'livraisons', 'countLivraions',  'getClientFromId', 'fournisseurs', 
            'commandeNotApproved' , 'magasins' , 'stocks', 'clients', 'hasPhysique', 'countApprovedSelledServices',  
            'countApprovedSelledProduct' , 'approvedSelledProduct' ,'countBusiness', 'hasPrestation' , 'countTeamMembers' , 
             'countTeams', 'countClients', 'user'));
        } else if(auth()->user()->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

        
    }



    public function dashboard_admin()
    {
        
        if(auth()->user()->type === 'admin_sys'){
            /* if (!Auth::user()->pricing_id) {
                return redirect()->route('pricing.page');
            } */
            
            if (!session()->has('active_tab')) {
                session(['active_tab' => 'service']);
            }

            $user = Auth::user();

            $businesses = $user->business()->paginate(10);

            $hasPhysique = Business::where('type', 'business_physique')->exists();

            $hasPrestation = Business::where('type', 'prestation_de_service')->exists();

            $categories = $user->categorieProduits;
            $fournisseurs = $user->fournisseurs;

            // Get the client by ID (for later use in views)
            $getClientFromId = function($id) {
                return Clients::find($id);
            };

            $countClients = count(Clients::all());
            $countTeams = count(Team::all());
            $countTeamMembers = count(TeamMember::all());
            $countBusiness = count(Business::all());
            $approvedSelledProduct = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            //->where('user_id' , auth()->id())
            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledProduct = count($approvedSelledProduct);
            $commandeApproved = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where service_id is null
            })
           // ->where('user_id' , auth()->id())
            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledServices = count($commandeApproved);

            $query  = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            //->where('user_id', $user->id)
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
            //->where('user_id', $user->id)
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
            $clients = Clients::all();
            $magasins = Magasins::all();
            //$stocks = Stock::all();
            // Start the query to fetch clients
            $query = Livraisons::all();
        
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
           // $livraisons = $query->paginate(10);
            $stocks = Stock::where('user_id' ,$user->id)->get();
            $countLivraions = count(Livraisons::all());


            return view('welcome_admin', compact('businesses' , 'categories' , 'countLivraions',  'getClientFromId', 'fournisseurs', 
            'commandeNotApproved' , 'magasins' , 'stocks', 'clients', 'hasPhysique', 'countApprovedSelledServices',  
            'countApprovedSelledProduct' , 'approvedSelledProduct' ,'countBusiness', 'hasPrestation' , 'countTeamMembers' , 
             'countTeams', 'countClients', 'user'));
        } else if(auth()->user()->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

        
    }

    public function updateTabSession(Request $request)
    {
        $tab = $request->query('tab', 'service');
        session(['active_tab' => $tab]);
        
        return response()->json(['success' => true]);
    }


    public function profilePage(){
        if(Auth::user()->type === 'client'){
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
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledProduct = count($approvedSelledProduct);
            $commandeApproved = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id' , auth()->id())
            ->where('validation_status' , 'approved')
            ->get(); 
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
            $countLivraions = count(Livraisons::where('user_id', $user->id)->get());


            return view('users.profile_page', compact('businesses' , 'categories' , 'livraisons', 'countLivraions',  'getClientFromId', 'fournisseurs', 'commandeNotApproved' , 'magasins' , 'stocks', 'clients', 'hasPhysique', 'countApprovedSelledServices',  'countApprovedSelledProduct' , 'approvedSelledProduct' ,'countBusiness', 'hasPrestation' , 'countTeamMembers' ,  'countTeams', 'countClients', 'user'));
        
        } else if(Auth::user()->type === 'team_member'){
            
            if (!session()->has('active_tab')) {
                session(['active_tab' => 'service']);
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
            $query  = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id', $teamBusinessOwner->id)
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
            ->where('user_id', $teamBusinessOwner->id)
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
            
    
            $countClients = count(Clients::where('user_id' ,$teamBusinessOwner->id)->get());
            $countTeams = count(Team::where('user_id' ,$teamBusinessOwner->id)->get());
            $countTeamMembers = count(TeamMember::where('user_id' ,$teamBusinessOwner->id)->get());
            $countBusiness = count(Business::where('user_id' ,$teamBusinessOwner->id)->get());
            $approvedSelledProduct = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
            })
            ->where('user_id' , $teamBusinessOwner->id)
            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledProduct = count($approvedSelledProduct);
            $commandeApproved = Commandes::whereHas('commandeItems', function ($query) {
                $query->whereNull('service_id'); // Filters CommandItems where service_id is null
            })
            ->where('user_id' , $teamBusinessOwner->id)
            ->where('validation_status' , 'approved')
            ->get(); 
            $countApprovedSelledServices = count($approvedSelledProduct);
            $approvedSelledProduct = $querypROD->paginate(10);
            $categories = $teamBusinessOwner->categorieProduits;
            $fournisseurs = $teamBusinessOwner->fournisseurs;
    
            // Get the client by ID (for later use in views)
            $getClientFromId = function($id) {
                return Clients::find($id);
            };
    
            $query = Livraisons::where('user_id', $teamBusinessOwner->id);
            
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
            $livraisons = $query->paginate(10);
            
            $teamAdminMarked = Team::where('name', 'admin')->where('user_id', $clientOwner->id)->first();
    
            $countTeamMembers = count(TeamMember::where('user_id' ,$teamBusinessOwner->id)->get());
            $clients = Clients::where('user_id' ,$teamBusinessOwner->id)->get();

            $user = User::where('email' , $realTeamMember->email)->first();
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
            $countLivraions = count(Livraisons::where('user_id', $teamBusinessOwner->id)->get());
            $services = Services::whereIn('business_id', $teamBusinessOwner->business->pluck('id'))->paginate(10);
            $stocks = Stock::where('user_id' ,$teamBusinessOwner->id)->get();
            $magasins = Magasins::where('user_id', $teamBusinessOwner->id)->paginate(10);


            return view('dashboard_team_member.profile_page', compact('businesses' , 'user', 'categories' , 'livraisons', 'countLivraions',  'getClientFromId', 'fournisseurs', 'commandeNotApproved' , 'magasins' , 'stocks', 'clients', 'hasPhysique', 'countApprovedSelledServices',  'countApprovedSelledProduct' , 'approvedSelledProduct' ,'countBusiness', 'hasPrestation' , 'countTeamMembers' ,  'countTeams', 'countClients', 'user'));

        }
    }


    public function team_member()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');

        }
        if (!session()->has('active_tab')) {
            session(['active_tab' => 'service']);
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
        $query  = Commandes::whereHas('commandeItems', function ($query) {
            $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
        })
        ->where('user_id', $teamBusinessOwner->id)
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
        ->where('user_id', $teamBusinessOwner->id)
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
        $user = User::where('email' , $realTeamMember->email)->first();


        $countClients = count(Clients::where('user_id' ,$teamBusinessOwner->id)->get());
        $countTeams = count(Team::where('user_id' ,$teamBusinessOwner->id)->get());
        $countTeamMembers = count(TeamMember::where('user_id' ,$teamBusinessOwner->id)->get());
        $countBusiness = count(Business::where('user_id' ,$teamBusinessOwner->id)->get());
        $approvedSelledProduct = Commandes::whereHas('commandeItems', function ($query) {
            $query->whereNull('stock_id'); // Filters CommandItems where stock_id is null
        })
        ->where('user_id' , $teamBusinessOwner->id)
        ->where('validation_status' , 'approved')
        ->get(); 
        $countApprovedSelledProduct = count($approvedSelledProduct);
        $commandeApproved = Commandes::whereHas('commandeItems', function ($query) {
            $query->whereNull('service_id'); // Filters CommandItems where service_id is null
        })
        ->where('user_id' , $teamBusinessOwner->id)
        ->where('validation_status' , 'approved')
        ->get(); 
        $countApprovedSelledServices = count($approvedSelledProduct);
        $approvedSelledProduct = $querypROD->paginate(10);
        $categories = $teamBusinessOwner->categorieProduits;
        $fournisseurs = $teamBusinessOwner->fournisseurs;

        // Get the client by ID (for later use in views)
        $getClientFromId = function($id) {
            return Clients::find($id);
        };

        $query = Livraisons::where('user_id', $teamBusinessOwner->id);
        
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
        $livraisons = $query->paginate(10);
        
        $teamAdminMarked = Team::where('name', 'admin')->where('user_id', $clientOwner->id)->first();

        $countTeamMembers = count(TeamMember::where('user_id' ,$teamBusinessOwner->id)->get());
        $clients = Clients::where('user_id' ,$teamBusinessOwner->id)->get();

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
        $countLivraions = count(Livraisons::where('user_id', $teamBusinessOwner->id)->get());
        $services = Services::whereIn('business_id', $teamBusinessOwner->business->pluck('id'))->paginate(10);
        $stocks = Stock::where('user_id' ,$teamBusinessOwner->id)->get();

        return view('welcome_team_member', compact('businesses', 'hasPhysique', 'hasPrestation' , 'realTeamMember',
            'countLivraions', 'countTeamMembers','clients', 'commandeNotApproved' ,
            'approvedSelledProduct' ,'stocks',
            'categories',
            'user',
            'fournisseurs',
            'getClientFromId','livraisons',
            'countApprovedSelledServices','services',  
                        'countApprovedSelledProduct' , 'approvedSelledProduct' ,'countBusiness', 
            'countTeams', 'countClients',
        ));
    }

}
