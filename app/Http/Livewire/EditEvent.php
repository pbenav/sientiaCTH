<?php

namespace App\Http\Livewire;

use App\Models\Event;

use Livewire\Component;

class EditEvent extends Component
{
    public $showModalGetTimeRegisters = false;

    public Event $event;

    protected $listeners = ['edit'];

    protected $rules = [
        'event.start' => 'required|date',
        'event.end' => 'required|date|after_or_equal:start_date',
        'event.description' => 'required',
    ];

    public function mount()
    {
        $this->event = new Event();
    }

    public function edit(Event $ev)
    {       
        // Modification is permitted only if event is open
        if ($ev->is_open == 1) {
            // and end date is empty
            if ($ev->end == "") {
                $ev->end = date('Y/m/d H:i:s');
            }
            $this->event = $ev;
            $this->showModalGetTimeRegisters = true;
            $this->emit('render');
        } else {
            $this->emit('alertFail', __("Event is confirmed."));
            $this->reset(["showModalGetTimeRegisters"]);
        }
    }

    public function update()
    {
        $this->validate();
        $this->event->save();
        $this->reset(["showModalGetTimeRegisters"]);
        $this->emit('alert', __('Event updated!'));
        $this->emitUp('render');
    }

    public function render()
    {
        return view('livewire.edit-event');
    }
}
