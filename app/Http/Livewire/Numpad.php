<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\LoginSecurityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * A Livewire component for a numeric keypad.
 *
 * This component provides a numpad interface for users to enter their code
 * and log in.
 */
class Numpad extends Component
{
    /**
     * Code input by the user.
     *
     * @var string
     */
    public string $user_code = '';

    /**
     * Indicates whether the numpad is open.
     *
     * @var bool
     */
    public bool $open = true;

    /**
     * Validates and processes the user code.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\LoginSecurityService $loginSecurityService
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function insertCode(Request $request, LoginSecurityService $loginSecurityService)
    {
        try {
            $loginSecurityService->check($request);
        } catch (ValidationException $e) {
            session()->flash('info', $e->validator->errors()->first());
            return;
        }

        // Basic code validation
        if (!preg_match('/^\d{4,10}$/', $this->user_code)) {
            session()->flash('info', __( 'Code format is invalid.' ));
            return;
        }

        // Ensure the code is available on the request for throttling keys
        $request->merge(['user_code' => $this->user_code]);

        $user = $this->findUserByCode($this->user_code);

        if (!$user) {
            $loginSecurityService->logFailedAttempt($request);
            session()->flash('info', __('Invalid code.'));
            return;
        }

        Auth::loginUsingId($user->id);
        $loginSecurityService->clearAttemptsOnSuccess($request);

        if (is_null($user->current_team_id)) {
            $user->switchTeam($user->personalTeam());
        }

        $events = $this->getOpenEventsForUser($user->id);

        // After authentication, redirect to Start menu (Inicio) where user can clock in/out
        if ($events->count() || $user->isTeamAdmin() || $user->isInspector()) {
            return redirect()->route('inicio');
        } else {
            // For users without open events, go to Start menu to clock in
            return redirect()->route('inicio');
        }

        $this->reset('user_code');
    }

    /**
     * Renders the numpad component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.numpad');
    }

    /**
     * Find a user by their code.
     *
     * @param string $code
     * @return \App\Models\User|null
     */
    private function findUserByCode(string $code): ?User
    {
        return User::where('user_code', $code)->first();
    }

    /**
     * Get the open events for a user.
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    private function getOpenEventsForUser(int $userId): \Illuminate\Support\Collection
    {
        return DB::table('events')
            ->where('user_id', $userId)
            ->where('is_open', "1")
            ->get();
    }
}
