<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use App\Traits\InsertHistory;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use PhpParser\Node\Expr\Cast\Bool_;

class EditEvent extends Component
{
    use InsertHistory;
    public $showModalEditEvent = false;

    public Event $event, $original_event;
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

    /**
     * Edit function
     *
     * @param Event $ev 
     * @return void
     */
    public function edit(Event $ev)
    {
        $this->event = $ev;
        $this->user = User::find($ev->user_id);
        // Modification is permitted only if event is open or if user is team admin

        if ($this->event->is_open == 1) {
            // if 'end' date is empty
            if (!$this->event->end) {
                $this->event->end = date('Y-m-d H:i:s');
            }
            $this->showModalEditEvent = true;
        } else {
            if (auth()->user()->isTeamAdmin()) {
                $this->showModalEditEvent = true;
            } else {
                $this->emit('alertFail', __("Event is confirmed."));
                $this->reset(["showModalEditEvent"]);
            }
        }
        $this->emitTo('get-time-registers', 'render');
    }

    public function update()
    {
        $this->original_event = clone $this->event;
        $this->validate();
        $this->event->save();
        if (auth()->user()->isTeamAdmin()) {
            // Write event on database to log this event using trait InsertHistory
            $this->insertHistory('events', $this->original_event, $this->event);
            unset($this->original_event);
        }
        $this->reset(["showModalEditEvent"]);
        $this->emit('alert', __('Event updated!'));
        $this->emitTo('get-time-registers', 'render');
    }

    public function render()
    {
        return view('livewire.events.edit-event');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
}
