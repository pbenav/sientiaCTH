<?php

namespace App\Http\Livewire;


use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddEvent extends Component
{
    public $showAddEventModal = false;

    public $now;
    public $start_date;
    public $start_time;
    public $user_id;
    public $description;

    protected $listeners = ['render', 'add'];

    protected $rules = [
        'start_date' => 'required',
        'start_time' => 'required',
        'description' => 'required'
    ];

    public function updated($propertyName){
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->start_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->description = '';
    }   

    public function add(){
        $this->showAddEventModal = true;
    }

    public function save()
    {
        $this->validate();

        Event::create([
            'start' => $this->start_date . ' ' . $this->start_time,
            'end' => null,
            'user_id' => Auth::user()->id,
            'user_code' => Auth::user()->user_code,
            'description' => $this->description,
            'is_open' => true
        ]);

        $this->reset([
            'showAddEventModal',

        ]);

        $this->emitTo('get-time-registers','render');
        $this->emit('alert', 'Event added!');
    }

    public function render()
    {
        return view('livewire.add-event');
    }   
}