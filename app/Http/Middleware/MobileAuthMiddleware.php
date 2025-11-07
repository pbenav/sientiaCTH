<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if mobile session is authenticated
        if (!session('mobile_authenticated', false) || !session('mobile_user_id')) {
            return redirect()->route('mobile.auth')->with('message', 'Please authenticate to access mobile features');
        }

        return $next($request);
    }
}
