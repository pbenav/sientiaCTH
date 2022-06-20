<?php

namespace App\Http\Livewire;

use Livewire\Component;

class RemoveEvent extends Component
{
    public $open = false;
    
    public function render()
    {
        return view('livewire.remove-event');
    }
}
