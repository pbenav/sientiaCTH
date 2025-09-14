<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Models\FailedLoginAttempt;
use Carbon\Carbon;

class LoginSecurityService
{
    /**
     * Check if the current IP is blocked.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function check(Request $request)
    {
        $ip = $request->ip();

        $lastAttempt = FailedLoginAttempt::where('ip_address', $ip)->latest('timestamp')->first();

        if ($lastAttempt && $lastAttempt->lockout_time && Carbon::now()->lessThan(Carbon::parse($lastAttempt->timestamp)->addSeconds($lastAttempt->lockout_time))) {
            $remaining = Carbon::parse($lastAttempt->timestamp)->addSeconds($lastAttempt->lockout_time)->diffInSeconds(Carbon::now());
            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', ['seconds' => $remaining])],
            ])->status(429);
        }
    }

    /**
     * Log a failed login attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function logFailedAttempt(Request $request)
    {
        $ip = $request->ip();
        $cacheKey = 'login-attempts:' . $ip;

        $attempts = Cache::get($cacheKey, 0);
        $attempts++;

        Cache::put($cacheKey, $attempts, config('security.login_delay.base') * config('security.login_delay.factor') * $attempts);

        if ($attempts > config('security.login_delay.max_attempts_before_hard_lock')) {
            $lockoutTime = config('security.login_delay.hard_lock_duration_in_hours') * 3600;
        } else {
            $lockoutTime = config('security.login_delay.base') * (config('security.login_delay.factor') ** ($attempts - 1));
        }

        FailedLoginAttempt::create([
            'ip_address' => $ip,
            'timestamp' => Carbon::now(),
            'lockout_time' => $lockoutTime,
        ]);
    }
}
