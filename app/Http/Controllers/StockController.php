<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Business;
use App\Models\MouvementDeStocks;
use App\Models\CategorieProduits;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use App\Models\Clients;
use App\Models\Commandes;
use App\Models\Livraisons;
use App\Models\Magasins;

class StockController extends Controller
{
    //
    public function showOwnerStockListPage(Request $request){

    }

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
    
        // Build the query for the stocks, starting with the basic query for the logged-in user
        $stocksQuery = Stock::where('user_id', $user->id);

        // Apply filter by 'name' if provided
        if ($request->has('name') && !empty($request->name)) {
            $stocksQuery->where('name', 'like', '%' . $request->name . '%');
        }

        // Apply filter by 'quantity' if provided
        if ($request->has('quantity') && !empty($request->quantity)) {
            $stocksQuery->where('quantity', '<=' ,  $request->quantity);
        }

        // Apply filter by 'price' if provided
        if ($request->has('price') && !empty($request->price)) {
            $stocksQuery->where('price' , '<=' , $request->price);
        }

        // Paginate the filtered stocks
        $stocks = $stocksQuery->paginate(10);
    
        
        $commandeTotalPerStock = function($stockId , $what_to_return) 
        {
            $countCommandeTotalPerCommande = 0;
            $countCommandeTotalMoneyPerCommande = 0;
            $countCommandeTotalBuyerCommande = 0;

            $stockApprovedCommandes = Commandes::whereHas('commandeItems', function ($query) use ($stockId) {
                $query->whereNull('service_id')
                      ->where('stock_id', $stockId); 
            })
            ->where('user_id', auth()->id())
            ->where('validation_status', 'approved')
            ->get();
            
            foreach ($stockApprovedCommandes as $commande) {
                foreach ($commande->commandeItems as $item) {
                    if ($item->stock_id == $stockId && $item->service_id === null) {
                        $countCommandeTotalPerCommande += $item->quantity; // Assuming price and quantity fields
                        $countCommandeTotalMoneyPerCommande +=  $commande->total_price;
                    }
                }
                $countCommandeTotalBuyerCommande += 1;
            }


            if($what_to_return === 'quantity'){
                return number_format($countCommandeTotalPerCommande);

            } else if($what_to_return === 'nombre_acheteur'){
                return number_format($countCommandeTotalBuyerCommande);

            } else {
                return number_format($countCommandeTotalMoneyPerCommande);

            }

        };
        
        $clientList = Clients::where('user_id', $user->id)->get();
        return view('users.statistiques.stocks', compact('stocks' , 'commandeTotalPerStock' , 'clientList', 'hasPhysique', 
            'hasPrestation', "businesses", 'user'));
    }


    public function getStat($stock){


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

            $countClients = count(Clients::where('user_id' ,$user->id)->get());
            $countTeams = count(Team::where('user_id' ,$user->id)->get());
            $countTeamMembers = count(TeamMember::where('user_id' ,$user->id)->get());
            $countBusiness = count(Business::where('user_id' ,$user->id)->get());
          

            $stocks = Stock::where('user_id' ,$user->id)->get();
            //$countLivraions = count($livraisonsForCount);
          
            $clients = Clients::where('user_id' , auth()->id())->paginate(10);


            $commandeTotalPerStock = function($clientId , $stock , $what_to_return) 
            {
                $commandeDatte = null;
                $countCommandeTotalMoneyPerCommande = 0;
                $countCommandeTotalBoughtByClient = 0;

                $stockApprovedCommandes = Commandes::whereHas('commandeItems', function ($query) use ($stock) {
                    $query->whereNull('service_id')
                        ->where('stock_id', $stock); 
                })
                ->where('user_id', auth()->id())
                ->where('validation_status', 'approved')
                ->get();
                
                foreach ($stockApprovedCommandes as $commande) {

                    foreach ($commande->commandeItems as $item) {
                        if ($commande->client_id == $clientId && $item->service_id === null) {
                            $countCommandeTotalBoughtByClient += $item->quantity;

                            $countCommandeTotalMoneyPerCommande += $commande->total_price;
                        }

                    }
                    
                }


                if($what_to_return === 'bought'){
                    return number_format($countCommandeTotalBoughtByClient);

                } else if($what_to_return === 'date'){
                    return $commandeDatte;

                } else {
                    return number_format($countCommandeTotalMoneyPerCommande);

                }

            };

            return view('users.statistiques.stock_detail', compact('businesses' , 'stock' , 'commandeTotalPerStock' , 'clients' ,  'categories', 'fournisseurs', 
           'stocks', 'hasPhysique',   
            'countBusiness', 'hasPrestation' , 'countTeamMembers' , 
             'countTeams', 'countClients', 'user'));
        } else if(auth()->user()->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

    }


    public function getStocksOfMagasin($magasin)
    {

        $magasin = Magasins::find($magasin);

        $pivotData = $magasin->stocks;


        return response()->json($pivotData);
    }


    public function index(Request $request)
    {
        if (auth()->user()->type === 'team_member') {
            //return redirect()->route('dashboard_team_member');
            $teamMember = Auth::user();
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

            
            $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
            /*  if (!$team) {
                abort(404, "Vous n'etes membre d'aucune equipe");
            } */

            
            //$intBus = Business::find(optional($team->pivot)->business_id);
            
            $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way
            $categories = $teamBusinessOwner->categorieProduits; 
            $fournisseurs = $teamBusinessOwner->fournisseurs; 
            
            $clientOwner = User::findOrFail($realTeamMember->user_id);

            $businesses = $teamBusinessOwner->business()->where('type', 'business_physique')->get(); 
            $hasPhysique = $teamBusinessOwner->business()->where('type', 'business_physique')->exists();
            $hasPrestation = $teamBusinessOwner->business()->where('type', 'prestation_de_service')->exists();
            view()->share('realTeamMember', $realTeamMember);


            $role = Role::find($realTeamMember->role_id);
            $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

            $role = Role::find($realTeamMember->role_id);
            view()->share('role', $formattedRole);
            view()->share('roleObj', $role);

            // Build the query for the stocks, starting with the basic query for the logged-in user
            $stocksQuery = Stock::where('user_id', $teamBusinessOwner->id);

            // Apply filter by 'name' if provided
            if ($request->has('name') && !empty($request->name)) {
                $stocksQuery->where('name', 'like', '%' . $request->name . '%');
            }

            // Apply filter by 'quantity' if provided
            if ($request->has('quantity') && !empty($request->quantity)) {
                $stocksQuery->where('quantity', '<=' ,  $request->quantity);
            }

            // Apply filter by 'price' if provided
            if ($request->has('price') && !empty($request->price)) {
                $stocksQuery->where('price' , '<=' , $request->price);
            }

            // Paginate the filtered stocks
            $stocks = $stocksQuery->paginate(10);

            $user = User::where('email' , $realTeamMember->email)->first();


            // Return the view with the filtered stocks and other necessary data
            return view('dashboard_team_member.stocks.index', compact(
                'stocks' , 'user', 'hasPhysique', 'hasPrestation', 'businesses', 'realTeamMember', 'categories', 'fournisseurs'
            ));
        }

        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business()->where('type', 'business_physique')->get(); 
        $categories = $user->categorieProduits; 
        $fournisseurs = $user->fournisseurs; 
        $magasins = $user->magasins; 

        // Build the query for the stocks, starting with the basic query for the logged-in user
        $stocksQuery = Stock::where('user_id', $user->id);

        // Apply filter by 'name' if provided
        if ($request->has('name') && !empty($request->name)) {
            $stocksQuery->where('name', 'like', '%' . $request->name . '%');
        }

        // Apply filter by 'quantity' if provided
        if ($request->has('quantity') && !empty($request->quantity)) {
            $stocksQuery->where('quantity', '<=' ,  $request->quantity);
        }

        // Apply filter by 'price' if provided
        if ($request->has('price') && !empty($request->price)) {
            $stocksQuery->where('price' , '<=' , $request->price);
        }

        // Paginate the filtered stocks
        $stocks = $stocksQuery->paginate(10);

        // Return the view with the filtered stocks and other necessary data
        return view('users.stocks.index', compact(
            'stocks', 'hasPhysique' , 'magasins', 'hasPrestation', 'businesses', 'user', 'categories', 'fournisseurs'
        ));
    }



    public function getMoveOfStocks(Request $request)
    {
        if (auth()->user()->type === 'team_member') {
            //return redirect()->route('dashboard_team_member');

            $teamMember = Auth::user();
            $realTeamMember = TeamMember::firstWhere('email', $teamMember->email);

            
            $team = $realTeamMember->team()->first();  // ✅ Correct way to access the relationship
            /*  if (!$team) {
                abort(404, "Vous n'etes membre d'aucune equipe");
            } */

            
            //$intBus = Business::find(optional($team->pivot)->business_id);
            
            $teamBusinessOwner = User::findOrFail($realTeamMember->user_id);  // ✅ Correct way
            $categories = $teamBusinessOwner->categorieProduits; 
            $fournisseurs = $teamBusinessOwner->fournisseurs; 
            
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

            // Retrieve all stocks associated with the user
            $stocks = Stock::where('user_id', $teamBusinessOwner->id)->get(); 

            // Start with the base query for stock movements
            $movesQuery = MouvementDeStocks::where('user_id', $teamBusinessOwner->id);

            // Apply filter by product if provided
            if ($request->has('product') && !empty($request->product)) {
                $movesQuery->where('stock_id', $request->product);
            }

            // Apply filter by type of movement if provided
            if ($request->has('type') && !empty($request->type)) {
                $movesQuery->where('type_de_mouvement', $request->type);
            }

            // Apply filter by start date if provided
            if ($request->has('date_start') && !empty($request->date_start)) {
                $movesQuery->where('created_at', '>=', $request->date_start);
            }

            // Apply filter by end date if provided
            if ($request->has('date_end') && !empty($request->date_end)) {
                $movesQuery->where('created_at', '<=', $request->date_end);
            }
            $user = User::where('email' , $realTeamMember->email)->first();

            // Paginate the filtered stock movements
            $moves = $movesQuery->paginate(10);
            return view('dashboard_team_member.stocks.moves', compact(
                'stocks', 'hasPhysique', 'hasPrestation', 'businesses', 'realTeamMember', 
                'categories', 'fournisseurs', 'moves' , 'user'
            ));
        }

        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $categories = $user->categorieProduits; 
        $fournisseurs = $user->fournisseurs; 

        // Retrieve all stocks associated with the user
        $stocks = Stock::where('user_id', $user->id)->get(); 

        // Start with the base query for stock movements
        $movesQuery = MouvementDeStocks::where('user_id', $user->id);

        // Apply filter by product if provided
        if ($request->has('product') && !empty($request->product)) {
            $movesQuery->where('stock_id', $request->product);
        }

        // Apply filter by type of movement if provided
        if ($request->has('type') && !empty($request->type)) {
            $movesQuery->where('type_de_mouvement', $request->type);
        }

        // Apply filter by start date if provided
        if ($request->has('date_start') && !empty($request->date_start)) {
            $movesQuery->where('created_at', '>=', $request->date_start);
        }

        // Apply filter by end date if provided
        if ($request->has('date_end') && !empty($request->date_end)) {
            $movesQuery->where('created_at', '<=', $request->date_end);
        }

        // Paginate the filtered stock movements
        $moves = $movesQuery->paginate(10);

        // Return the view with the filtered stock movements and necessary data
        return view('users.stocks.moves', compact(
            'stocks', 'hasPhysique', 'hasPrestation', 'businesses', 'user', 
            'categories', 'fournisseurs', 'moves'
        ));
    }

    public function retrait_magasin(Request $request){
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
            'magasin_id' => 'required|exists:magasins,id',
        ]);
    
        // Find the magasin
        $magasin = Magasins::find($request->magasin_id);
        // Find the stock
        $stock = Stock::find($request->stock_id);

        
        // Check if the stock is already attached to the magasin
        $pivotData = $magasin->stocks()->where('stock_id', $stock->id)->first();
        
        if ($pivotData) {
            if($pivotData->pivot->quantity<$request->quantity){
                return redirect()->back()->with('error', 'Quantité souhaité inférieur a la quantité disponible!');
            }
            // If the product already exists, increase the quantity
            $currentQuantity = $pivotData->pivot->quantity;
            $newQuantity = $currentQuantity - $request->quantity;
    
            // Update the pivot table with the new quantity
            $magasin->stocks()->updateExistingPivot($stock->id, [
                'quantity' => $newQuantity
            ]);
    
        } 
        

        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

            if($isAuthTeamMemberQuestionMark->type === 'client'){
                MouvementDeStocks::create([
                    'stock_id' => $stock->id,
                    'magasin_id' => $request->magasin_id, //fournisseur id is magasin id in this case
                    'quantity' => $request->quantity,
                    'prix_fournisseur' => 0.00,
                    'user_id' => Auth::id(),
                    'type_de_mouvement' => env('RETRAIT_MAGASIN'),
                    'files_paths' => null
                ]);
            }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
                
                $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
                //dd(Auth::id());
                //dd( $test->user_id);
                MouvementDeStocks::create([
                    'stock_id' => $stock->id,
                    'magasin_id' => $request->magasin_id, //fournisseur id is magasin id in this case
                    'quantity' => $request->quantity,
                    'prix_fournisseur' => 0.00,
                    'user_id' => $test->user_id,
                    'type_de_mouvement' => env('RETRAIT_MAGASIN'),
                    'files_paths' => null
                ]);
            }

        $stock->quantity += $request->quantity;
        $stock->save();

        return redirect()->back()->with('success', 'Quantité mise à jour avec succès!');

    }

    public function bonus_fournisseur(Request $request){
        $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'stock_id' => 'required|exists:stocks,id', 
            'quantity' => 'required|numeric|min:1',
            'factures_achat' => 'required|array', 
            'factures_achat.*' => 'mimes:pdf,jpeg,jpg,png|max:2048',
        ]);
    
        // Retrieve the product/stock by stock_id
        $stock = Stock::findOrFail($request->stock_id); // Corrected 'business_id' to 'stock_id'
    
        // Update the stock quantity
        $stock->quantity += $request->quantity;
        $stock->save();
    
        // Handle file uploads
        $filePaths = [];
        if ($request->hasFile('factures_achat')) {
            foreach ($request->file('factures_achat') as $file) {
                // Store each file in the 'factures_add_up_quantity' directory inside 'public' disk
                $filePath = $file->store('factures_add_up_quantity', 'public');
                $filePaths[] = $filePath; // Save the file path in the array for later use
            }
        }
    
       

        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

        if($isAuthTeamMemberQuestionMark->type === 'client'){
            
            MouvementDeStocks::create([
                'stock_id' => $stock->id,
                'fournisseur_id' => $request->fournisseur_id,
                'prix_fournisseur' => 0.00,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(),
                'type_de_mouvement' => env('BONUS_FOURNISSEUR'),
                'files_paths' => json_encode($filePaths)
            ]);
        }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {

            $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
            MouvementDeStocks::create([
                'stock_id' => $stock->id,
                'fournisseur_id' => $request->fournisseur_id,
                'quantity' => $request->quantity,
                'user_id' => $test->user_id, 
                'type_de_mouvement' => env('BONUS_FOURNISSEUR'),
                'files_paths' => json_encode($filePaths)
            ]);
           

        }
    
        return back()->with('success', 'Quantité mise à jour avec succès et factures téléchargées!');
    
    }


    public function add_up_quantity(Request $request)
    {
        
        $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'stock_id' => 'required|exists:stocks,id', 
            'prix_fournisseur' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:1',
            'factures_achat' => 'required|array', 
            'factures_achat.*' => 'mimes:pdf,jpeg,jpg,png|max:2048',
        ]);
    
        // Retrieve the product/stock by stock_id
        $stock = Stock::findOrFail($request->stock_id); // Corrected 'business_id' to 'stock_id'
    
        // Update the stock quantity
        $stock->quantity += $request->quantity;
        $stock->save();
    
        // Handle file uploads
        $filePaths = [];
        if ($request->hasFile('factures_achat')) {
            foreach ($request->file('factures_achat') as $file) {
                // Store each file in the 'factures_add_up_quantity' directory inside 'public' disk
                $filePath = $file->store('factures_add_up_quantity', 'public');
                $filePaths[] = $filePath; // Save the file path in the array for later use
            }
        }
    
       

        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

        if($isAuthTeamMemberQuestionMark->type === 'client'){
            
            MouvementDeStocks::create([
                'stock_id' => $stock->id,
                'fournisseur_id' => $request->fournisseur_id,
                'prix_fournisseur' => $request->prix_fournisseur,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(),
                'type_de_mouvement' => env('ACHAT_DE_STOCK'),
                'files_paths' => json_encode($filePaths)
            ]);
        }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {

            $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
            MouvementDeStocks::create([
                'stock_id' => $stock->id,
                'fournisseur_id' => $request->fournisseur_id,
                'quantity' => $request->quantity,
                'user_id' => $test->user_id, 
                'type_de_mouvement' => env('ACHAT_DE_STOCK'),
                'files_paths' => json_encode($filePaths)
            ]);
           

        }
    
        return back()->with('success', 'Quantité mise à jour avec succès et factures téléchargées!');
    }
    



      public function store(Request $request)
      {
          $request->validate([
              'name' => 'required|max:255',
              'description' => 'nullable|max:1000',
              'category_id' => 'required|exists:categorie_produits,id',
              'price' => 'required|numeric|min:0',
              'business_id' => 'required|exists:business,id',
          ]);

          $isAuthTeamMemberQuestionMark = User::find(Auth::id());

          if($isAuthTeamMemberQuestionMark->type === 'client'){
            
            Stock::create([
                'name' => $request->name,
                'description' => $request->description,
                'quantity' => 0,
                'quantite_inventorie' => 0,
                'category_id'=> $request->category_id,
                'price' => $request->price,
                'business_id' => $request->business_id,
                'user_id' => Auth::id(), 
            ]);
          }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {

            $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
            Stock::create([
                'name' => $request->name,
                'description' => $request->description,
                'quantity' => 0,
                'quantite_inventorie' => 0,
                'category_id'=> $request->category_id,
                'price' => $request->price,
                'business_id' => $request->business_id,
                'user_id' => $test->user_id, 
            ]);

          }
  
          
  
          return redirect()->back()->with('success', 'Stock ajouté avec succès!');
      }
  
      public function edit($id)
      {
          $stock = Stock::findOrFail($id);
          //$businesses = Business::all(); 
          return response()->json([
            'stock'       => $stock,
          ]);
      }
  
      public function update(Request $request, $id)
      {
          $stock = Stock::findOrFail($id);
  
          $request->validate([
              'name' => 'required|max:255',
              'description' => 'nullable|max:1000',
              //'quantity' => 'required|integer|min:0',
              'price' => 'required|numeric|min:0',
              'category_id' => 'required|exists:categorie_produits,id',
              'business_id' => 'required|exists:business,id',
          ]);
  
          $stock->update([
              'name' => $request->name,
              'description' => $request->description,
              'category_id'=> $request->category_id,
              //'quantity' => $request->quantity,
              'price' => $request->price,
              'business_id' => $request->business_id,
          ]);
  
          return redirect()->back()->with('success', 'Stock mis à jour avec succès!');
      }
  
      public function destroy($id)
      {
          $stock = Stock::findOrFail($id);
          $stock->delete();
  
          return redirect()->back()->with('success', 'Stock supprimé avec succès!');
      }
  
      public function makeInventory($id)
      {
          $stock = Stock::findOrFail($id);
          return view('stock.inventory', compact('stock'));
      }
  
      public function confirmInventory(Request $request, $id)
      {
          $stock = Stock::findOrFail($id);
  
          $request->validate([
              'real_quantity' => 'required|integer|min:0',
          ]);
  
          if ($stock->quantity != $request->real_quantity) {
              $stock->quantity = $request->real_quantity; 
              $stock->save();
              return redirect()->route('stock.listes')->with('success', 'Inventaire confirmé, stock mis à jour!');
          }
  
          return redirect()->back()->with('success', 'La quantité de l\'inventaire est identique, aucune mise à jour nécessaire.');
      }
}
