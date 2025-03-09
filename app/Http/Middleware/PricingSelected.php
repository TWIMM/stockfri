<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login'); // Prevents calling Auth::user() on null
        }

        $user = Auth::user();

        if ($user->type === 'team_member') {
            return redirect()->route('dashboard_team_member');
        }

        // Redirect clients without pricing to the pricing page
        if ($user->type === 'client' && !$user->pricing_id) {
            return redirect()->route('pricing.page');
        }

        // Only redirect non-clients if they are NOT already on the dashboard
        if ($user->type !== 'client' && !$request->is('dashboard')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

