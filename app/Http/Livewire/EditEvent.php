<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EditEvent extends Component
{
    public $showModalEditEvent = false;

    public Event $event;
    public User $user;

    protected $listeners = ['edit'];

    protected $rules = [
        'event.start' => 'required|date',
        'event.end' => 'required|date',
        'event.description' => 'required',
        'event.observations' => 'string|max:255|nullable',
    ];

    public function mount()
    {
        $this->event = new Event();
        $this->user = User::find(Auth::user()->id);
    }

    public function edit(Event $ev)
    {
        $this->event = $ev;
        $this->user = User::find($ev->user_id);
        error_log('Modificando evento ' . $this->event);
        // Modification is permitted only if event is open or if user is team admin
        // In this case, there must write a change event into log
        if ($this->event->is_open == 1 || $this->user->isTeamAdmin()) {
            // and end date is empty
            if (!$this->event->end) {
                $this->event->end = date('Y-m-d H:i:s');
            }
            $this->showModalEditEvent = true;            
        } else {
            $this->emit('alertFail', __("Event is confirmed."));
            $this->reset(["showModalEditEvent"]);
        }
        $this->emitTo('get-time-registers', 'render');
    }

    public function update()
    {
        $this->validate();
        $this->event->save();
        $this->reset(["showModalEditEvent"]);
        $this->emit('alert', __('Event updated!'));
        $this->emitTo('get-time-registers', 'render');
    }

    public function render()
    {
        return view('livewire.edit-event');
    }

    public function updated($propertyName){
        $this->validateOnly($propertyName);
    }
}