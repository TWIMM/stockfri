<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coequipier;

class CoequipierController extends Controller
{
    public function index()
    {
        $coequipiers = Coequipier::paginate(10);
        return view('coequipiers.index', compact('coequipiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:coequipiers,email',
            'tel' => 'required|string|max:20',
        ]);

        Coequipier::create($request->all());

        return redirect()->route('coequipiers.index')->with('success', 'Coequipier ajouté avec succès.');
    }

    public function update(Request $request, Coequipier $coequipier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:coequipiers,email,' . $coequipier->id,
            'tel' => 'required|string|max:20',
        ]);

        $coequipier->update($request->all());

        return redirect()->route('coequipiers.index')->with('success', 'Coequipier mis à jour avec succès.');
    }

    public function destroy(Coequipier $coequipier)
    {
        $coequipier->delete();

        return redirect()->route('coequipiers.index')->with('success', 'Coequipier supprimé avec succès.');
    }
}
