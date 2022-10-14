<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use DateTime;
use Livewire\Component;

use function PHPUnit\Framework\isNull;

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
        $this->event = $ev;        
        // Modification is permitted only if event is open
        if ($this->event->is_open == 1) {
            // and end date is empty
            if (isNull($this->event->end)) {
                $this->event->end = date('Y-m-d H:i:s');
            }
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
