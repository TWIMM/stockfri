<?php

namespace App\Http\Controllers;

use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicesController extends Controller
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

        $services = Services::paginate(10); 
        return view('users.services.index', compact('services','hasPhysique', 
            'hasPrestation', "businesses",  'user'));
    }

    public function showOwnerServicesListPage()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');
        }

        //$services = Services::where('business_id', auth()->user()->business_id)->get();
        return view('dashboard_team_member.services.owner_index', compact('services'));
    }

    public function create()
    {
        if(auth()->user()->type === 'client'){
            return redirect()->route('dashboard');
        }

        return view('dashboard_team_member.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:services,title|string|max:255',
            'description' => 'required|string|max:1000',
            'business_id' => 'exists:business,id',
            'price' => 'required|numeric',
        ]);

        $service = new Services();
        //$service->business_id = auth()->user()->business_id;
        $service->title = $request->title;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->quantity = 0;
        $service->business_id = $request->business_id;
        $service->save();

        return back()->with('success', 'Service created successfully');
    }



    public function edit($id)
    {
        $service = Services::findOrFail($id);

        return response()->json([
            'service'       => $service,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric',
        ]);

        $service = Services::findOrFail($id);
        $service->title = $request->title;
        $service->description = $request->description;
        $service->price = $request->price;

        $service->save();

        return back()->with('success', 'Service updated successfully');
    }

    public function destroy($id)
    {
        $service = Services::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service deleted successfully');
    }
}
