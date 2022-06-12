<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Numpad extends Component
{
    public $user_code = '';    

    public function addNumber($number)
    {
        if (strlen($this->user_code) <= 10) {
            $this->user_code .= $number;
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

    public function render()
    {
        return view('livewire.numpad');
    }
}
