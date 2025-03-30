<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class FournisseurController extends Controller
{
    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
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
            'email' => 'required|email|unique:fournisseurs,email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'ifu' => 'required|string',

        ]);

        Fournisseur::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'ifu' => $request->ifu ? $request->ifu : null,
            'user_id' => Auth::id(), 
        ]);

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
            'email' => 'required|email|unique:fournisseurs,email,' . $id,
            'phone' => 'required|string',
            'address' => 'required|string',
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
