<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EditEvent extends Component
{
    public $event;
    public $open = false;

    protected $rules = [
        'event.end_time' => 'required',
        'event.description' => 'required',
    ];

    public function mount(Event $event){
        $this->event = $event;
    }

    public function save(){
        $this->validate();
        $this->event->save();

        $this->reset(["open"]);

        $this->emitTo('get-time-registers','render');
        $this->emit('alert', 'Event updated!');
    }

    public function render()
    {        
        return view('livewire.edit-event');
    }
}