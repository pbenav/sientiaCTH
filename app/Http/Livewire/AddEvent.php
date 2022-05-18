<?php

namespace App\Http\Livewire;


use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddEvent extends Component
{
    public $open = false;

    public $now;
    public $startTime;
    public $startDate;
    public $endTime;
    public $userId;
    public $description;

    protected $rules = [
        'startTime' => 'required',
        'endTime' => 'required',
    ];

    public function updated($propertyName){
        $this->validateOnly($propertyName);
    }


    public function init()
    {
        $this->startDate = date('Y-m-d');
        $this->startTime = date('H:i:s');
        $this->endTime = date('Y-m-d');
        $this->description = 'Add a description';
    }


    public function save()
    {
        $this->validate();

        Event::create([
            'startTime' => $this->startDate . ' ' . $this->startTime,
            'endTime' => $this->endTime,
            'userId' => Auth::user()->id,
            'description' => $this->description
        ]);


        $this->reset([
            'open',
            'startDate',
            'startTime',
            'endTime',
            'userId',
            'description'
        ]);

        $this->emitTo('get-local-time','render');
        $this->emit('alert', 'Event added!');
    }

    public function render()
    {
        return view('livewire.add-event');
    }
}
