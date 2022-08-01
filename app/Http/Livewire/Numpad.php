<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class Numpad extends Component
{
    public $user_code = '';
    public $show_Numpad_Modal = false;

    public function addCode($code)
    {
        if (strlen($this->user_code) <= 10) {
            $this->user_code .= $code;
        }
    }

    public function resetDialer()
    {
        $this->user_code = '';
    }

    public function delete()
    {
        if (strlen($this->user_code) > 0) {
            $this->user_code = substr($this->user_code, 0, -1);
        }
    }

    public function insertCode()
    {
        $user = DB::table('users')->where('user_code', $this->user_code)->first();
        if ($user) {
            Auth::loginUsingId($user->id);
            $events = DB::table('events')->where('user_id', $user->id)->where('is_open', "1")->get();
            return redirect()->to('/dashboard');            
        } else {
            error_log('No se ha encontrado el código de usuario');
        }

        $this->resetDialer();
        $this->render();
    }

    public function render()
    {
        return view('livewire.numpad');
    }
}
