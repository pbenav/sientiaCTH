<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Auth;

class AddEventModal extends Component
{
    public $showModal = false;
    public $eventTypes = [];
    public $event_type_id;
    public $description;
    public $start_date;
    public $end_date;

    protected $listeners = ['showAddEventModal'];

    public function render()
    {
        $this->eventTypes = EventType::all();
        return view('livewire.add-event-modal');
    }

    public function showAddEventModal($date)
    {
        $this->start_date = $date;
        $this->end_date = $date;
        $this->showModal = true;
    }

    public function createEvent()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'event_type_id' => 'required|exists:event_types,id',
        ]);

        Event::create([
            'user_id' => Auth::id(),
            'description' => $this->description,
            'start' => $this->start_date,
            'end' => $this->end_date,
            'event_type_id' => $this->event_type_id,
            'is_open' => true,
        ]);

        $this->emit('refreshCalendar');
        $this->showModal = false;
    }
}
