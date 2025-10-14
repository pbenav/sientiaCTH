<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $user->load('events.workCenter');
        $schedule = $user->meta->where('meta_key', 'schedule')->first();
        if ($schedule) {
            $user->schedule = json_decode($schedule->meta_value);
        }
        $user->last_5_events = $user->events->sortByDesc('start')->take(5);

        return response()->json($user);
    }
}
