<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth/login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate login inputs
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Attempt to login the user with the provided credentials
        if (Auth::attempt($validated)) {
            // Redirect user to the dashboard or pricing page
            // Check if pricing is selected, redirect accordingly
            if(auth()->user()->type === 'client'){
                if (auth()->user()->pricing_id) {
                    return redirect()->route('dashboard');
                }
    
                // Redirect user to the pricing page if no pricing selected
                return redirect()->route('pricing.page');
            } else if(auth()->user()->type === 'team_member'){
                return redirect()->route('dashboard_team_member');
            }
        }

        // If login fails, redirect back with an error message
        return Redirect::back()->withErrors([
            'email' => 'Invalidees donnes de connexions',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
