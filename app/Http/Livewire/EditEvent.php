<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use App\Traits\InsertHistory;
use App\Traits\HasWorkScheduleHint;
use Illuminate\Support\Facades\Auth;

class EditEvent extends Component
{
    use InsertHistory, HasWorkScheduleHint;

    /**
     * @var bool $showModalEditEvent Determines if the edit event modal is visible.
     */
    public $showModalEditEvent = false;

    public $workScheduleHint = '';

    /**
     * @var Event $event Holds the event being edited.
     * @var Event $original_event Holds the original state of the event for logging.
     */
    public Event $event, $original_event;

    /**
     * @var User $user Holds the user associated with the event.
     */
    public User $user;
    public $eventTypes;

    /**
     * @var array $listeners Listens for emitted events.
     */
    protected $listeners = ['edit'];

    /**
     * @var array $rules Validation rules for the event.
     */
    protected $rules = [
        'event.start' => 'required|date',
        'event.end' => 'required|date',
        'event.description' => 'required',
        'event.event_type_id' => 'required',
        'event.observations' => 'string|max:255|nullable',
    ];

    /**
     * Initialize component with default values.
     *
     * @return void
     */
    public function mount()
    {
        $this->event = new Event();
        $this->user = User::find(Auth::user()->id);
        $this->eventTypes = collect();
    }

    /**
     * Handles editing of a specific event.
     *
     * @param Event $ev The event to edit.
     * @return void
     */
    public function edit(Event $ev)
    {
        $this->event = $ev;
        $this->original_event = clone $ev;
        $this->user = User::find($ev->user_id);
        $this->eventTypes = collect();
        if ($this->event->eventType && $this->event->eventType->team) {
            $this->eventTypes = $this->event->eventType->team->eventTypes;
        } else if ($this->user->currentTeam) {
            $this->eventTypes = $this->user->currentTeam->eventTypes;
        }

        $this->setWorkScheduleHint();

        if ($this->event->is_open == 1) {
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

    /**
     * Updates the event details and logs changes if applicable.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        $this->event->save();

        if (auth()->user()->isTeamAdmin()) {
            $this->insertHistory('events', $this->original_event, $this->event);
            unset($this->original_event);
        }

        $this->reset(["showModalEditEvent"]);
        $this->emit('alert', __('Event updated!'));
        $this->emitTo('get-time-registers', 'render');
    }

    /**
     * Renders the Livewire view for editing events.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.events.edit-event');
    }

    /**
     * Validates a specific property when it is updated.
     *
     * @param string $propertyName The name of the property being updated.
     * @return void
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
}
