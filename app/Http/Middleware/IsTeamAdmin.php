<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeamAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $team = $request->route('team');

        if (!$team && $request->route('work_center')) {
            $team = $request->route('work_center')->team;
        }

        if (!auth()->user()->isTeamAdmin($team)) {
            abort(403);
        }

        return $next($request);
    }
}
