<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Magasins;

class MagasinsController extends Controller
{
    //

    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
        }
        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 
        $categories = $user->categorieProduits; 
        $fournisseurs = $user->fournisseurs; 
        $businesses = $user->business; 

        $magasins = Magasins::where('user_id' ,$user->id)->paginate(10);
        return view('users.magasins.index', compact('magasins','hasPhysique', 
            'hasPrestation', "businesses",  'user' , "categories" , "fournisseurs"));
    }


    public function store(Request $request)
    {
          $request->validate([
              'name' => 'required|max:255',
              'description' => 'nullable|max:1000',
              'tel' => 'required|string',
              'address' => 'required|string',
              'email' => 'required|string',
              'business_id' => 'required|exists:business,id',
          ]);
  
          Magasins::create([
              'name' => $request->name,
              'description' => $request->description,
              'address' => $request->address,

              'tel' =>  $request->tel,
              'email' => $request->email,
              'business_id' => $request->business_id,
              'user_id' => Auth::id(), 
          ]);
  
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
    
            return redirect()->back()->with('success', 'Quantité mise à jour avec succès!');
        } else {
            // If the product is not yet added, attach it with the given quantity
            $magasin->stocks()->attach($stock->id, ['quantity' => $request->quantity]);
    
            return redirect()->back()->with('success', 'Produit ajouté au magasin!');
        }
    }
}
