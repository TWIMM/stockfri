<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
              'category_id' => 'required|exists:categorie_produits,id',
              'price' => 'required|numeric|min:0',
              'business_id' => 'required|exists:business,id',
          ]);
  
          Stock::create([
              'name' => $request->name,
              'description' => $request->description,
              'quantity' => 0,
              'quantite_inventorie' => 0,
              'category_id'=> $request->category_id,
              'price' => $request->price,
              'user_id' => Auth::id(), 
              'business_id' => $request->business_id,
          ]);
  
          return redirect()->back()->with('success', 'Stock ajouté avec succès!');
    }
}
