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

        return view('users.creence_clients.index', compact('clients','hasPhysique', 
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
