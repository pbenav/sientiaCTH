<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ExceptionalClockInToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExceptionalClockInController extends Controller
{
    /**
     * Handle the exceptional clock-in request.
     *
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clockIn($token)
    {
        $tokenRecord = ExceptionalClockInToken::where('token', $token)->first();

        if (!$tokenRecord) {
            return redirect()->route('dashboard')->with('error', __('exceptional_clock_in.invalid_link'));
        }

        if ($tokenRecord->used_at) {
            return redirect()->route('dashboard')->with('error', __('exceptional_clock_in.used_link'));
        }

        if (Carbon::now()->isAfter($tokenRecord->expires_at)) {
            return redirect()->route('dashboard')->with('error', __('exceptional_clock_in.expired_link'));
        }

        // Authenticate the user associated with the token to ensure all policies and ownerships are respected.
        Auth::loginUsingId($tokenRecord->user_id);
        $user = Auth::user();
        $team = $tokenRecord->team;

        // Find the default workday event type for the team
        $workdayEventType = $team->eventTypes()->where('is_workday_type', true)->first();

        if (!$workdayEventType) {
             return redirect()->route('dashboard')->with('error', __('exceptional_clock_in.no_workday_event_type'));
        }

        $defaultWorkCenter = $user->meta->where('meta_key', 'default_work_center_id')->first();
        $defaultWorkCenterId = $defaultWorkCenter ? $defaultWorkCenter->meta_value : null;

        // Check if there is an open event to close it (clock-out)
        $openEvent = Event::where('user_id', $user->id)->where('is_open', true)->orderBy('start', 'desc')->first();

        if ($openEvent) {
            // Clock-out: Close the existing event
            $openEvent->update([
                'end' => Carbon::now(config('app.timezone'))->setTimezone('UTC'),
                'is_open' => false,
            ]);
        } else {
            // Clock-in: Create a new event
            Event::create([
                'user_id' => $user->id,
                'work_center_id' => $defaultWorkCenterId,
                'description' => $workdayEventType->name,
                'event_type_id' => $workdayEventType->id,
                'start' => Carbon::now(config('app.timezone'))->setTimezone('UTC'),
                'end' => null,
                'is_open' => true,
                'is_authorized' => false,
            ]);
        }

        // Mark the token as used
        $tokenRecord->update(['used_at' => Carbon::now()]);

        return redirect()->route('events')->with('success', __('exceptional_clock_in.success'));
    }
}
