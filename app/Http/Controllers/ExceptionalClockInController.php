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
            return redirect()->route('events')->with('error', __('exceptional_clock_in.invalid_link'));
        }

        if ($tokenRecord->used_at) {
            return redirect()->route('events')->with('error', __('exceptional_clock_in.used_link'));
        }

        if (Carbon::now()->isAfter($tokenRecord->expires_at)) {
            return redirect()->route('events')->with('error', __('exceptional_clock_in.expired_link'));
        }

        // The token is valid, redirect to the regularization form
        return redirect()->route('exceptional.clock-in.form', ['token' => $token]);
    }
}
