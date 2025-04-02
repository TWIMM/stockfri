<?php

namespace App\Http\Controllers;

use App\Models\Magasins;
use App\Models\Stock;
use App\Models\MouvementDeStocks;
use App\Models\Clients;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
class MagasinsController extends Controller
{
    //

    public function index()
    {
        if(auth()->user()->type === 'team_member'){
           // return redirect()->route('dashboard_team_member');
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

            $categories = $teamBusinessOwner->categorieProduits; 
            $fournisseurs = $teamBusinessOwner->fournisseurs; 
            $stocks = Stock::where('user_id' ,$teamBusinessOwner->id)->get();

            // Build query to filter magasins
            $query = Magasins::where('user_id', $teamBusinessOwner->id);

            // Apply filters if the input is provided
            if ($name = request('search')) {
                $query->where('name', 'like', "%{$name}%");
            }

            if ($quantity = request('email')) {
                $query->where('email', '>=', $quantity); // Filter magasins where quantity is greater than or equal to the value
            }

            if ($price = request('tel')) {
                $query->where('tel', '<=', $price); // Filter magasins where price is less than or equal to the value
            }

            // Get the filtered results with pagination
            $magasins = $query->paginate(10);
            $user = User::where('email' , $realTeamMember->email)->first();

            return view('dashboard_team_member.magasins.index', compact('magasins', 'user', 'hasPhysique', 'hasPrestation', 'businesses', 'realTeamMember', 'categories', 'fournisseurs', 'stocks'));

        }

        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $categories = $user->categorieProduits; 
        $fournisseurs = $user->fournisseurs; 
        $stocks = Stock::where('user_id' ,$user->id)->get();

        // Build query to filter magasins
        $query = Magasins::where('user_id', $user->id);

        // Apply filters if the input is provided
        if ($name = request('search')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($quantity = request('email')) {
            $query->where('email', '>=', $quantity); // Filter magasins where quantity is greater than or equal to the value
        }

        if ($price = request('tel')) {
            $query->where('tel', '<=', $price); // Filter magasins where price is less than or equal to the value
        }

        // Get the filtered results with pagination
        $magasins = $query->paginate(10);

        return view('users.magasins.index', compact('magasins', 'hasPhysique', 'hasPrestation', 'businesses', 'user', 'categories', 'fournisseurs', 'stocks'));
    }

    public function showList($id){ 

        if(auth()->user()->type === 'team_member'){
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

            $categories = $teamBusinessOwner->categorieProduits; 
            $fournisseurs = $teamBusinessOwner->fournisseurs; 
            $role = Role::find($realTeamMember->role_id);
            $formattedRole = $role ? Str::title(str_replace('_', ' ', $role->name)) : 'Unknown';

            $role = Role::find($realTeamMember->role_id);
            view()->share('role', $formattedRole);
            view()->share('roleObj', $role);
            $magasin = Magasins::find($id);
            $magasins = Magasins::where('user_id' ,$teamBusinessOwner->id)->paginate(10);

            $stocksQuery = DB::table('magasin_stock')
            ->where('magasin_id', $id);

            /* if ($name = request('name')) {
                $stocksQuery->where('name', 'like', "%" . $name . "%");
            } */
            if ($quantity = request('quantity')) {
                $stocksQuery->where('quantity', '>=', $quantity);  // Filters stocks with quantity greater than or equal to the value
            }
            /* if ($price = request('price')) {
                $stocksQuery->where('price', '>=', $price);
            } */
            $stocksOfMagasin = $stocksQuery->get();
            $stocks = Stock::where('user_id' ,$teamBusinessOwner->id)->get();
            $stocksArray = [];
            foreach ($stocksOfMagasin as $magasinStock) {
                // Fetch the related Stock record based on the stock_id in magasin_stock
                $stock = Stock::find($magasinStock->stock_id); // Assuming stock_id exists in magasin_stock
        
                // If stock exists, push it into the array (formatted to the desired structure)
                if ($stock) {
                    if ($name = request('name')) {
                        if (stripos($stock->name, $name) === false) {
                            continue;  // Skip this stock if the name does not match
                        }
                    }
        
                    // Filter by 'price' if the 'price' filter is provided
                    if ($price = request('price')) {
                        if ($stock->price < $price) {
                            continue;  // Skip this stock if the price is less than the provided value
                        }
                    }
                    $stocksArray[] = (object)[
                        'id' => $magasinStock->id,
                        'magasin_id' => $magasinStock->magasin_id, 
                        'name' => $stock->name, 
                        'quantity' => $magasinStock->quantity,
                        'price' => $stock->price,
                        //'description' => $stock->created_at,
                        //'updated_at' => $stock->updated_at,
                    ];
                }
            }
            $user = User::where('email' , $realTeamMember->email)->first();
            $clients = Clients::where('user_id' ,$teamBusinessOwner->id)->get();
            return view('dashboard_team_member.magasins.details', compact('magasin', 'magasins','hasPhysique', 
            'hasPrestation', "businesses", 'clients',  'realTeamMember' , 'user' , "categories" , "fournisseurs" , "stocks" , "stocksArray"));
        }
        $magasin = Magasins::find($id);

        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $categories = $user->categorieProduits; 
        $fournisseurs = $user->fournisseurs; 
        $businesses = $user->business; 
        $magasins = Magasins::where('user_id' ,$user->id)->paginate(10);


        $magasin = Magasins::find($id);
        $stocksQuery = DB::table('magasin_stock')
            ->where('magasin_id', $id);

        /* if ($name = request('name')) {
            $stocksQuery->where('name', 'like', "%" . $name . "%");
        } */
        if ($quantity = request('quantity')) {
            $stocksQuery->where('quantity', '>=', $quantity);  // Filters stocks with quantity greater than or equal to the value
        }
        /* if ($price = request('price')) {
            $stocksQuery->where('price', '>=', $price);
        } */
        $stocksOfMagasin = $stocksQuery->get();
        $stocks = Stock::where('user_id' ,$user->id)->get();
        $stocksArray = [];
        foreach ($stocksOfMagasin as $magasinStock) {
            // Fetch the related Stock record based on the stock_id in magasin_stock
            $stock = Stock::find($magasinStock->stock_id); // Assuming stock_id exists in magasin_stock
    
            // If stock exists, push it into the array (formatted to the desired structure)
            if ($stock) {
                if ($name = request('name')) {
                    if (stripos($stock->name, $name) === false) {
                        continue;  // Skip this stock if the name does not match
                    }
                }
    
                // Filter by 'price' if the 'price' filter is provided
                if ($price = request('price')) {
                    if ($stock->price < $price) {
                        continue;  // Skip this stock if the price is less than the provided value
                    }
                }
                $stocksArray[] = (object)[
                    'id' => $magasinStock->id,
                    'magasin_id' => $magasinStock->magasin_id, 
                    'name' => $stock->name, 
                    'quantity' => $magasinStock->quantity,
                    'price' => $stock->price,
                    //'description' => $stock->created_at,
                    //'updated_at' => $stock->updated_at,
                ];
            }
        }
        $clients = Clients::where('user_id' ,$user->id)->get();

       // dd($stocksArray);
        return view('users.magasins.details', compact('magasin', 'magasins','hasPhysique', 
            'hasPrestation', "businesses", 'clients',  'user' , "categories" , "fournisseurs" , "stocks" , "stocksArray"));

    }
    



    public function store(Request $request)
    {
          $request->validate([
              'name' => 'required|max:255',
              'description' => 'nullable|max:1000',
              'tel' => 'required|string',
              'address' => 'required|string',
              'email' => 'required|string|unique:magasins,email',
              'business_id' => 'required|exists:business,id',
          ]);

            $isAuthTeamMemberQuestionMark = User::find(Auth::id());

            if($isAuthTeamMemberQuestionMark->type === 'client'){
                Magasins::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'address' => $request->address,
      
                    'tel' =>  $request->tel,
                    'email' => $request->email,
                    'business_id' => $request->business_id,
                    'user_id' => Auth::id(), 
                ]);
            }else if ($isAuthTeamMemberQuestionMark->type === 'team_member') {
                
                $test = TeamMember::where('email' ,  $isAuthTeamMemberQuestionMark->email)->first();
                //dd(Auth::id());
                //dd( $test->user_id);
                Magasins::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'address' => $request->address,
      
                    'tel' =>  $request->tel,
                    'email' => $request->email,
                    'business_id' => $request->business_id,
                    'user_id' => $test->user_id, 
                ]);
            }
  
          
  
          return redirect()->back()->with('success', 'Magasin ajouté avec succès!');
    }

    public function addStockProduitToMagasin(Request $request){
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
            'magasin_id' => 'required|exists:magasins,id',
        ]);
    
        // Find the magasin
        $magasin = Magasins::find($request->magasin_id);
        // Find the stock
        $stock = Stock::find($request->stock_id);

        if($stock->quantity<$request->quantity){
            return redirect()->back()->with('error', 'Quantité souhaité inférieur a la quantité disponible!');
        }

        
        // Check if the stock is already attached to the magasin
        $pivotData = $magasin->stocks()->where('stock_id', $stock->id)->first();
        
        if ($pivotData) {
            // If the product already exists, increase the quantity
            $currentQuantity = $pivotData->pivot->quantity;
            $newQuantity = $currentQuantity + $request->quantity;
    
            // Update the pivot table with the new quantity
            $magasin->stocks()->updateExistingPivot($stock->id, [
                'quantity' => $newQuantity
            ]);
    
        } else {
            // If the product is not yet added, attach it with the given quantity
            $magasin->stocks()->attach($stock->id, ['quantity' => $request->quantity]);
    
        }

        

        $isAuthTeamMemberQuestionMark = User::find(Auth::id());

            if($isAuthTeamMemberQuestionMark->type === 'client'){
                MouvementDeStocks::create([
                    'stock_id' => $stock->id,
                    'magasin_id' => $request->magasin_id, //fournisseur id is magasin id in this case
                    'quantity' => $request->quantity,
                    'user_id' => Auth::id(),
                    'type_de_mouvement' => env('ENVOI_VERS_MAGASIN'),
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
                    'user_id' => $test->user_id,
                    'type_de_mouvement' => env('ENVOI_VERS_MAGASIN'),
                    'files_paths' => null
                ]);
            }

        $stock->quantity -= $request->quantity;
        $stock->save();

        return redirect()->back()->with('success', 'Quantité mise à jour avec succès!');

    }
}
