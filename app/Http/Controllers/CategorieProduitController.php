<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class CategorieProduitController extends Controller
{
    public function index(Request $request)
    {
        // Check if the user is a 'team_member' and redirect them if necessary
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
        }

        // Get the authenticated user
        $user = Auth::user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 

        // Start building the query for categories
        $categoriesQuery = CategorieProduits::where('user_id', $user->id);

        // Apply the search filter if a 'search' parameter is provided
        if ($request->has('search') && !empty($request->search)) {
            $categoriesQuery->where('name', 'like', '%' . $request->search . '%');  // Searching by category name
        }

        // You can add more filters if needed, such as filtering by type, etc.
        // Example:
        if ($request->has('type') && !empty($request->type)) {
            $categoriesQuery->where('type', $request->type);  // Filter by category type (or any other field)
        }

        // Paginate the categories with the applied filters
        $categories = $categoriesQuery->paginate(10);

        // Return the view with the filtered categories
        return view('users.categorie_produits.index', compact(
            'categories', 'hasPhysique', 'hasPrestation', 'businesses', 'user'
        ));
    }


    // Afficher le formulaire de création
    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        CategorieProduits::create([
            'name' => $request->name,
            'user_id' => Auth::id(), 
        ]);

        return redirect()->back()->with('success', 'Catégorie ajoutée avec succès!');
    }


    // Afficher le formulaire de modification d'une catégorie
    public function edit($id)
    {
        $category = CategorieProduits::find($id);

        if (!$category) {
            return response()->json([
                'category' => json_encode([]),
            ]);
        }

        return response()->json([
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = CategorieProduits::find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'Catégorie introuvable.');
        }

        // Mise à jour des informations de la catégorie
        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Catégorie mise à jour avec succès!');
    }

    public function destroy($id)
    {
        $category = CategorieProduits::find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'Catégorie introuvable.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Catégorie supprimée avec succès!');
    }
}
