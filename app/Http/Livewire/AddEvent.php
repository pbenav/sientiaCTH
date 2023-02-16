<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddEvent extends Component
{
    public $showAddEventModal = false;
    public $goDashboardModal = false;

    public $now;
    public $start_date;
    public $start_time;
    public $user_id;
    public $description;
    public $origin;

    protected $listeners = ['add'];

    protected $rules = [
        'start_date' => 'required|after:-1 day|before:+1 day', // no more than one day before
        'start_time' => 'required|after:-15 minutes|before:+15 minutes', // |after_or_equal:now', When needed!!!
        'description' => 'required'
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->start_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->description = __('Workday');
    }

    public function add($origin)
    {
        $this->origin = $origin;
        $this->showAddEventModal = true;
    }

    public function cancel()
    {
        $this->showAddEventModal = false;
        $this->redirect('/events');
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

        if ($this->origin == 'numpad') {
            return redirect()->route('events')->with('info', 'E_SUCCESS');
        } else { 
            $this->emitTo('get-time-registers', 'render');        
        }
    }

    public function render()
    {
        return view('livewire.add-event');
    }
}