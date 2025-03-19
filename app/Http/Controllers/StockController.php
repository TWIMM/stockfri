<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\MouvementDeStocks;


class StockController extends Controller
{
    //
    public function showOwnerStockListPage(Request $request){

    }


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

        $stocks = Stock::where('user_id' ,$user->id)->paginate(10); 
        return view('users.stocks.index', compact('stocks','hasPhysique', 
            'hasPrestation', "businesses",  'user' , "categories" , "fournisseurs"));
    }

    public function add_up_quantity(Request $request)
    {
        
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
    
        MouvementDeStocks::create([
            'stock_id' => $stock->id,
            'fournisseur_id' => $request->fournisseur_id,
            'quantity' => $request->quantity,
            'user_id' => Auth::id(),
            'type_de_mouvement' => env('ACHAT_DE_STOCK'),
            'files_paths' => json_encode($filePaths)
        ]);
    
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
              'quantity' => 'required|integer|min:0',
              'price' => 'required|numeric|min:0',
              'category_id' => 'required|exists:categorie_produits,id',
              'business_id' => 'required|exists:businesses,id',
          ]);
  
          $stock->update([
              'name' => $request->name,
              'description' => $request->description,
              'category_id'=> $request->category_id,
              'quantity' => $request->quantity,
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
