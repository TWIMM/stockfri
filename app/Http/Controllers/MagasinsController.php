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
}
