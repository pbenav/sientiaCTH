<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Auth;

class EditEventModal extends Component
{
    public $showModal = false;
    public $event;
    public $eventTypes = [];
    public $event_type_id;
    public $description;
    public $start_date;
    public $end_date;

    protected $listeners = ['showEditEventModal'];

    public function render()
    {
        $this->eventTypes = EventType::all();
        return view('livewire.edit-event-modal');
    }

    public function showEditEventModal($eventId)
    {
        $this->event = Event::find($eventId);
        $this->description = $this->event->description;
        $this->start_date = $this->event->start;
        $this->end_date = $this->event->end;
        $this->event_type_id = $this->event->event_type_id;
        $this->showModal = true;
    }

    public function updateEvent()
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'event_type_id' => 'required|exists:event_types,id',
        ]);

        if ($this->event) {
            $this->event->update([
                'description' => $this->description,
                'start' => $this->start_date,
                'end' => $this->end_date,
                'event_type_id' => $this->event_type_id,
            ]);
        }

        $this->emit('refreshCalendar');
        $this->showModal = false;
    }

    public function deleteEvent()
    {
        if ($this->event) {
            $this->event->delete();
        }

        $this->emit('refreshCalendar');
        $this->showModal = false;
    }
}
