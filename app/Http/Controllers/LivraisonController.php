<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livraisons;

class LivraisonController extends Controller
{
    //

    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
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
    
        // Apply the 'tel' (telephone) filter if provided
        if ($tel = request('tel')) {
            $query->where('tel', 'like', "%" . $tel . "%");
        }
    
        // Get the filtered clients and paginate the results
        $livraisons = $query->paginate(10);
    
        return view('users.livraisons.index', compact('livraisons', 'hasPhysique', 
            'hasPrestation', "businesses", 'user'));
    }
}
