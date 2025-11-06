<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;
use Livewire\Component;
use App\Traits\InsertHistory;
use App\Traits\HasWorkScheduleHint;
use App\Traits\HandlesEventAuthorization;
use Illuminate\Support\Facades\Auth;

/**
 * A Livewire component for editing existing events.
 *
 * This component provides a modal form for editing events, including
 * authorization checks and history logging.
 */
class EditEvent extends Component
{
    use InsertHistory, HasWorkScheduleHint, HandlesEventAuthorization;

    /**
     * Determines if the event can be modified by the current user.
     *
     * @var bool
     */
    public bool $canBeModified = false;

    /**
     * Determines if the edit event modal is visible.
     *
     * @var bool
     */
    public bool $showModalEditEvent = false;

    /**
     * A hint about the user's work schedule.
     *
     * @var string
     */
    public string $workScheduleHint = '';

    /**
     * Holds the collection of event types.
     *
     * @var \Illuminate\Support\Collection
     */
    public $eventTypes;

    /**
     * @var Event $event Holds the event being edited.
     * @var Event $original_event Holds the original state of the event for logging.
     */
    public Event $event, $original_event;

    /**
     * The start and end date/time of the event.
     *
     * @var string
     */
    public $start_date, $end_date, $start_time, $end_time, $start_datetime, $end_datetime;

    /**
     * Holds the user associated with the event.
     *
     * @var User
     */
    public User $user;

    /**
     * The event listeners for the component.
     *
     * @var array
     */
    protected $listeners = ['edit'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        if (isset($this->event->eventType) && $this->event->eventType->is_all_day) {
            return [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'event.observations' => 'nullable|string|max:255',
            ];
        }

        return [
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required',
            'event.observations' => 'nullable|string|max:255',
        ];
    }

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->event = new Event();
        $this->user = User::find(Auth::user()->id);
        $this->eventTypes = collect();
        
        // Initialize all date/time fields
        $this->start_date = '';
        $this->end_date = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->start_datetime = '';
        $this->end_datetime = '';
    }

    /**
     * Show the edit event modal.
     *
     * @param \App\Models\Event $ev
     * @return void
     */
    public function edit(Event $ev): void
    {
        $this->event = $ev->load('workCenter');
        $this->original_event = clone $ev;
        $this->user = User::find($ev->user_id);

        // Populate the properties for the form, converting from UTC to the app's timezone
        $startCarbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ev->start, 'UTC')
            ->setTimezone(config('app.timezone'));
        
        $this->start_datetime = $startCarbon->toDateTimeLocalString();
        $this->start_date = $startCarbon->format('Y-m-d');
        $this->start_time = $startCarbon->format('H:i');

        if ($ev->end) {
            $endCarbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ev->end, 'UTC')
                ->setTimezone(config('app.timezone'));
            
            $this->end_datetime = $endCarbon->toDateTimeLocalString();
            $this->end_date = $endCarbon->format('Y-m-d');
            $this->end_time = $endCarbon->format('H:i');
        } else {
            $nowCarbon = \Carbon\Carbon::now(config('app.timezone'));
            $this->end_datetime = $nowCarbon->toDateTimeLocalString();
            $this->end_date = $nowCarbon->format('Y-m-d');
            $this->end_time = $nowCarbon->format('H:i');
        }

        // Force refresh the component to ensure values are updated
        $this->dispatchBrowserEvent('refresh-edit-modal');

        if ($this->event->is_exceptional) {
            $workScheduleMeta = $this->user->meta()->where('meta_key', 'work_schedule')->first();
            $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

            $eventStartTime = Carbon::parse($this->start_datetime);
            $dayOfWeek = $eventStartTime->format('N');
            $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
            $dayAbbr = $dayMap[$dayOfWeek] ?? null;

            $todaysSlots = collect($schedule)->filter(function ($slot) use ($dayAbbr) {
                return in_array($dayAbbr, $slot['days']);
            });

            if ($todaysSlots->isNotEmpty()) {
                $closestSlot = null;
                $smallestDiff = null;

                foreach ($todaysSlots as $slot) {
                    $slotStart = Carbon::parse($eventStartTime->format('Y-m-d') . ' ' . $slot['start']);
                    $diff = abs($eventStartTime->timestamp - $slotStart->timestamp);

                    if (is_null($smallestDiff) || $diff < $smallestDiff) {
                        $smallestDiff = $diff;
                        $closestSlot = $slot;
                    }
                }

                if ($closestSlot) {
                    $this->start_datetime = Carbon::parse($eventStartTime->format('Y-m-d') . ' ' . $closestSlot['start'])->toDateTimeLocalString();
                    $this->end_datetime = Carbon::parse($eventStartTime->format('Y-m-d') . ' ' . $closestSlot['end'])->toDateTimeLocalString();
                }
            }
        }

        $this->setWorkScheduleHint();

        $this->canBeModified = $this->canModifyEvent($this->event);

        if ($this->canBeModified) {
            $this->showModalEditEvent = true;
        } else {
            $this->emit('alertFail', __("Event is confirmed."));
            $this->reset(["showModalEditEvent"]);
        }

        $this->emitTo('get-time-registers', 'render');
    }

    /**
     * Update the event.
     *
     * @return void
     */
    public function update(): void
    {
        if (!$this->canModifyEvent($this->event)) {
            $this->emit('alertFail', __("You are not authorized to perform this action."));
            $this->reset(["showModalEditEvent"]);
            return;
        }

        $this->validate();

        // If it's an exceptional event and has observations, prepend "Exceptional event:"
        if ($this->event->is_exceptional && !empty($this->event->observations)) {
            // Only add the prefix if it doesn't already have it
            $exceptionalPrefix = __('exceptional_event.prefix');
            if (!str_starts_with($this->event->observations, $exceptionalPrefix)) {
                $this->event->observations = $exceptionalPrefix . ' ' . $this->event->observations;
            }
        }

        if ($this->event->eventType && $this->event->eventType->is_all_day) {
            $this->event->start = Carbon::parse($this->start_date, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $this->event->end = Carbon::parse($this->end_date, config('app.timezone'))
                ->addDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        } else {
            // Combine separate date and time fields
            $startDateTime = $this->start_date . ' ' . $this->start_time;
            $endDateTime = $this->end_date . ' ' . $this->end_time;
            
            $this->event->start = Carbon::parse($startDateTime, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $this->event->end = Carbon::parse($endDateTime, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        // If description is empty, use event type name
        if (empty($this->event->description) && $this->event->eventType) {
            $this->event->description = $this->event->eventType->name;
        }

        // Update is_extra_hours based on new logic: only main workday events are NOT overtime
        if ($this->event->eventType) {
            $this->event->is_extra_hours = !$this->event->eventType->is_workday_type;
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
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.events.edit-event');
    }

    /**
     * Validate a single property.
     *
     * @param string $propertyName
     * @return void
     */
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Delete an event.
     *
     * @param int $eventId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(int $eventId)
    {
        $event = Event::find($eventId);
        if ($event) {
            $event->delete();
        }

        session()->flash('alert', __('Event has been removed!'));
        return redirect()->route('calendar');
    }
}
