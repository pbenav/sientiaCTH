<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has is_admin property or admin role
        if ((property_exists($user, 'is_admin') && $user->is_admin) || 
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            return $next($request);
        }

        abort(403, __('Unauthorized action'));
    }
}
