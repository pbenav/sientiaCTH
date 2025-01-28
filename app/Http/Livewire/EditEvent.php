<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use App\Traits\InsertHistory;
use Illuminate\Support\Facades\Auth;

class EditEvent extends Component
{
    use InsertHistory;

    /**
     * @var bool $showModalEditEvent Determines if the edit event modal is visible.
     */
    public $showModalEditEvent = false;

    /**
     * @var Event $event Holds the event being edited.
     * @var Event $original_event Holds the original state of the event for logging.
     */
    public Event $event, $original_event;

    /**
     * @var User $user Holds the user associated with the event.
     */
    public User $user;

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
        $this->user = User::find($ev->user_id);

        if ($this->event->is_open == 1) {
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

    /**
     * Updates the event details and logs changes if applicable.
     *
     * @return void
     */
    public function update()
    {
        $this->original_event = clone $this->event;
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
