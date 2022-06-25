<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class GetTimeRegisters extends Component
{

    public $search;
    public $event;
    public $open_edit = false;
    public $sort = 'start_time';
    public $direction = 'desc';

    protected $listeners = ['render'];   
    
    protected $rules = [
        'event.end_time' => 'required',
        'event.description' => 'required',
    ];

    public function render()
    {
        $events = Event::where('description', 'like', '%' . $this->search . '%')
        ->where('user_id', '=', Auth::user()->id)
        ->orderBy($this->sort, $this->direction)
        ->get();
        return view('livewire.get-time-registers', compact('events'));
    }

    public function order($sort)
    {
        if ($this->sort = $sort) {
            if ($this->direction == 'asc') {
                $this->direction = 'desc';
            } else {
                $this->direction = 'asc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        };
    }

    public function edit($event){
        $this->event = $event;
        $this->open_edit = true;
    }

    public function update(){
        $this->validate();
        $this->event->save();

        $this->reset(["open_edit"]);

        $this->emitTo('get-time-registers','render');
        $this->emit('alert', 'Event updated!');
    }
}
