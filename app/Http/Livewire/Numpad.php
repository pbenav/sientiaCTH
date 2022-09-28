<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\Event;
use App\Models\User;

class Numpad extends Component
{
    public $user_code = '';
    public $open = true; 

    public function insertCode()
    {
        $user = DB::table('users')->where('user_code', $this->user_code)->first();
        if ($user) {
            Auth::loginUsingId($user->id);
            $events = DB::table('events')->where('user_id', $user->id)->where('is_open', "1")->get();
            # If there are no open events call to AddEvent
            if ($events->count()) {
                return redirect()->route('dashboard');
            } else {
                $this->emitTo('add-event', 'add', 'numpad');                
            }
        } else {
            return redirect()->route('front')->with('info', 'E_ERRORCODE');
        }
        $this->reset('user_code');        
    }

    public function render()
    {        
        return view('livewire.numpad');
    }
}
