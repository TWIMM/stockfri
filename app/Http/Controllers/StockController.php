<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;


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

        $stocks = Stock::paginate(10); 
        return view('users.stocks.index', compact('stocks','hasPhysique', 
            'hasPrestation', "businesses",  'user'));
    }


      public function store(Request $request)
      {
          $request->validate([
              'name' => 'required|max:255',
              'description' => 'nullable|max:1000',
              //'quantity' => 'required|integer|min:0',
              'price' => 'required|numeric|min:0',
              'business_id' => 'required|exists:business,id',
          ]);
  
          Stock::create([
              'name' => $request->name,
              'description' => $request->description,
              'quantity' => 0,
              'quantite_inventorie' => 0,

              'price' => $request->price,
              'business_id' => $request->business_id,
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
              'business_id' => 'required|exists:businesses,id',
          ]);
  
          $stock->update([
              'name' => $request->name,
              'description' => $request->description,
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
