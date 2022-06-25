<?php

namespace App\Http\Livewire;


use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddEvent extends Component
{
    public $open = false;

    public $now;
    public $start_date;
    public $start_time;
    public $user_id;
    public $description;

    protected $rules = [
        'start_date' => 'required',
        'start_time' => 'required',
        'description' => 'required|string|max:150'
    ];

    public function updated($propertyName){
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        date_default_timezone_set(env('APP_TIMEZONE'));
        $this->start_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->description = '';
    }

    public function save()
    {
        $this->validate();

        Event::create([
            'start_time' => $this->start_date . ' ' . $this->start_time,
            'end_time' => null,
            'user_id' => Auth::user()->id,
            'user_code' => Auth::user()->user_code,
            'description' => $this->description,
            'is_open' => true
        ]);

        $this->reset([
            'open',

        ]);

        $this->emitTo('get-time-registers','render');
        $this->emit('alert', 'Event added!');
    }

    public function render()
    {
        return view('livewire.add-event');
    }
}