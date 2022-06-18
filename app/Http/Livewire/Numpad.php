<?php

namespace App\Http\Livewire;

use Livewire\Component;

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
        echo "Insertando código" . $this->user_code;

    }

    public function render()
    {
        return view('livewire.numpad');
    }
}
