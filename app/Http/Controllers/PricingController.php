<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingController extends Controller
{
    
    
    /**
     * Show the pricing page.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPricingPage()
    {
        $monthlyPricings = Pricing::where('periodicity', 'monthly')->get();
        $annualPricings = Pricing::where('periodicity', 'yearly')->get();
        
        return view('auth/pricing', compact('monthlyPricings', 'annualPricings'));
    }


    /**
     * Handle the selected pricing for the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function selectPricing(Request $request)
    {
        $request->validate([
            'pricing_id' => 'required|exists:pricings,id',
        ]);

        $user = Auth::user();
        $user->pricing_id = $request->pricing_id;
        $user->save();

        return response()->json(['message' => 'Pricing selected successfully!']);
    }
}
