<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Numpad extends Component
{
    /**
     * @var string $user_code Code input by the user.
     */
    public $user_code = '';

    /**
     * @var bool $open Indicates whether the numpad is open.
     */
    public $open = true;

    /**
     * Validates and processes the user code.
     *
     * @return \Illuminate\Http\RedirectResponse|null Redirects to the appropriate route based on validation results.
     */
    public function insertCode()
    {
        // Basic code validation
        if (!preg_match('/^\d{4,10}$/', $this->user_code)) {
            session()->flash('info', __( 'Code format is invalid.' ));
            return;
        }

        $user = $this->findUserByCode($this->user_code);

        if (!$user) {
            session()->flash('info', __('Invalid code.'));
            return;
        }

        Auth::loginUsingId($user->id);

        $events = $this->getOpenEventsForUser($user->id);

        if ($user) {
            
            // If there are no open events, or the user has specific roles, redirect or emit an event
            if ($events->count() || $user->isTeamAdmin() || $user->isInspector()) {
                return redirect()->route('events');
            } else {
                $this->emitTo('add-event', 'add', 'numpad');
            }
        } else {
            return redirect()->route('front')->with('info', 'E_ERRORCODE');
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

    private function findUserByCode($code)
    {
        return User::where('user_code', $code)->first();
    }

    private function getOpenEventsForUser($userId)
    {
        return DB::table('events')
            ->where('user_id', $userId)
            ->where('is_open', "1")
            ->get();
    }
}
