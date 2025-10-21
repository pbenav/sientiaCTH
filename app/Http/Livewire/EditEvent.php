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
        if (isset($this->event->eventType) && $this->event->eventType->is_all_day) {
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
        $this->event = $ev->load('workCenter');
        $this->original_event = clone $ev;
        $this->user = User::find($ev->user_id);

        // Populate the new properties for the form, converting from UTC to the app's timezone
        $this->start_datetime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ev->start, 'UTC')
            ->setTimezone(config('app.timezone'))
            ->toDateTimeLocalString();
        $this->start_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ev->start, 'UTC')
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d');

        if ($ev->end) {
            $this->end_datetime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ev->end, 'UTC')
                ->setTimezone(config('app.timezone'))
                ->toDateTimeLocalString();
            $this->end_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ev->end, 'UTC')
                ->setTimezone(config('app.timezone'))
                ->format('Y-m-d');
        } else {
            $this->end_datetime = \Carbon\Carbon::now(config('app.timezone'))->toDateTimeLocalString();
            $this->end_date = \Carbon\Carbon::now(config('app.timezone'))->format('Y-m-d');
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
            $this->event->start = Carbon::parse($this->start_date, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $this->event->end = Carbon::parse($this->end_date, config('app.timezone'))
                ->addDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        } else {
            $this->event->start = Carbon::parse($this->start_datetime, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $this->event->end = Carbon::parse($this->end_datetime, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        $this->event->save();

        if (auth()->user()->isTeamAdmin()) {
            $this->insertHistory('events', $this->original_event, $this->event);
            unset($this->original_event);
        }

        $this->reset(["showModalEditEvent"]);
        $this->dispatchBrowserEvent('swal:alert', [
            'title' => __('Success'),
            'text' => __('Event updated!'),
            'icon' => 'success',
        ]);
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

    public function delete($eventId)
    {
        $event = Event::find($eventId);
        if ($event) {
            $event->delete();
        }

        session()->flash('alert', __('Event has been removed!'));
        return redirect()->route('calendar');
    }
}
