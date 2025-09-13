<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;
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
     * @var \Illuminate\Support\Collection $eventTypes Holds the collection of event types.
     */
    public $eventTypes;

    /**
     * @var Event $event Holds the event being edited.
     * @var Event $original_event Holds the original state of the event for logging.
     */
    public Event $event, $original_event;
    public $start_date, $end_date, $start_datetime, $end_datetime;

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
    protected function rules()
    {
        if ($this->event->eventType && $this->event->eventType->is_all_day) {
            return [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'event.observations' => 'nullable|string|max:255',
            ];
        }

        return [
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'event.observations' => 'nullable|string|max:255',
        ];
    }

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

        // Populate the new properties for the form
        $this->start_datetime = \Carbon\Carbon::parse($ev->start)->toDateTimeLocalString();
        $this->start_date = \Carbon\Carbon::parse($ev->start)->format('Y-m-d');

        if ($ev->end) {
            $this->end_datetime = \Carbon\Carbon::parse($ev->end)->toDateTimeLocalString();
            $this->end_date = \Carbon\Carbon::parse($ev->end)->format('Y-m-d');
        } else {
            $this->end_datetime = \Carbon\Carbon::now()->toDateTimeLocalString();
            $this->end_date = \Carbon\Carbon::now()->format('Y-m-d');
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

        if ($this->event->eventType && $this->event->eventType->is_all_day) {
            $this->event->start = $this->start_date . ' 00:00:00';
            $this->event->end = Carbon::parse($this->end_date)->addDay()->format('Y-m-d H:i:s');
        } else {
            $this->event->start = $this->start_datetime;
            $this->event->end = $this->end_datetime;
        }

        if (auth()->user()->isTeamAdmin() && $this->event->eventType && $this->event->eventType->is_all_day) {
            $this->event->is_open = 0;
        }

        $this->event->save();

        if (auth()->user()->isTeamAdmin()) {
            $this->insertHistory('events', $this->original_event, $this->event);
            unset($this->original_event);
        }

        $this->reset(["showModalEditEvent"]);
        $this->emit('alert', __('Event updated!'));
        $this->emitTo('get-time-registers', 'render');
        $this->emit('refreshCalendar');
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

    public function delete()
    {
        if ($this->event) {
            $this->event->delete();
        }

        $this->reset(["showModalEditEvent"]);
        $this->emit('alert', __('Event has been removed!'));
        $this->emit('refreshCalendar');
    }
}
