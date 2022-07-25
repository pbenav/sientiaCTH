<?php

namespace App\Http\Livewire;

use Livewire\Component;
Use App\Models\Event;
Use App\Models\User;
use Illuminate\Support\Facades\DB;

class Numpad extends Component
{
    public $user_code = '';    

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

    public function insertCode(){

        $event = null;

        $user = DB::table('users')->where('user_code', $this->user_code);
        //$user = User::where('user_code', $this->user_code)->get();
        if ($user === 1) {
            echo 'Buscando eventos...';
            $event = DB::table('event')
            ->where('is_open', '=', '1')
            ->get();
            dd($event);
        } else {
            dump($event);
            dd($user);
        }

        $this->resetDialer();
        $this->render();
    }

    public function render()
    {
        return view('livewire.numpad');
    }
}
