<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
        }
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 

        $clients = Clients::where('user_id' ,$user->id)->paginate(10);
        return view('users.clients.index', compact('clients','hasPhysique', 
        'hasPrestation', "businesses",  'user'));
    }

    public function handleDebt( Request $request){
        $request->validate([
            'id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            //'file' => 'mimes:pdf,jpeg,jpg,png|max:2048', 
            'factures_achat' => 'required|array', 
            'factures_achat.*' => 'mimes:pdf,jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('factures_achat')) {
            foreach ($request->file('factures_achat') as $file) {
                // Store each file in the 'factures_add_up_quantity' directory inside 'public' disk
                $filePath = $file->store('factures_remboursements', 'public');
                $filePaths[] = $filePath; // Save the file path in the array for later use
            }
        }


        $client = Clients::find($request->id);

        if($request->amount > $client->current_debt){
            $client->current_debt = 0; 
        } else {
            $client->current_debt = $client->current_debt - $request->amount; 

        }
        $client->save();
        
        return response()->json($client);
    }


    public function getDette()
    {
        if(auth()->user()->type === 'team_member'){
            return redirect()->route('dashboard_team_member');
        }
        $user = auth()->user();
        $hasPhysique = $user->business()->where('type', 'business_physique')->exists();
        $hasPrestation = $user->business()->where('type', 'prestation_de_service')->exists();
        $businesses = $user->business; 

        $clients = Clients::where('user_id' ,$user->id)
        ->where('current_debt' , '>' , 0)
        //->where('limit_credit_for_this_user' , '<=' , 0)
        ->paginate(10);

        return view('users.finances.creence_clients', compact('clients','hasPhysique', 
        'hasPrestation', "businesses",  'user'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function edit( $id)
    {
        $client = Clients::find($id);
        return response()->json($client);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'tel' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        Clients::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'email' => $request->email,
            'tel' => $request->tel,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Success');
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $id,
            'tel' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);
        $client = Clients::find($id);


        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'tel' => $request->tel,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Success');
    }

    public function destroy($id)
    {
        $client = Clients::find($id);

        $client->delete();

        return redirect()->back()->with('success', 'Success');
    }
}
