<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EditEvent extends Component
{
    public $showModalGetTimeRegisters = false;

    public Event $event;

    protected $listeners = ['edit'];

    protected $rules = [
        'event.start' => 'required|date', // Whenever is in production add: |after_or_equal:today',
        'event.end' => 'required|date|after_or_equal:event.start',
        'event.description' => 'required',
    ];

    public function mount()
    {
        $this->event = new Event();
    }
    
    public function edit(Event $ev)
    {       
        error_log('Modificando evento ' . $ev->id);       
        // Modification is permitted only if event is open or if user is team admin
        // In this case, there must write a change event into log
        if ($ev->is_open == 1 || Auth::user()->isTeamAdmin()) {
            // and end date is empty
            if (!$ev->end) {
                $ev->end = date('Y-m-d H:i:s');
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
