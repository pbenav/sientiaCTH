<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\EventType;
use App\Notifications\EventCreated;
use App\Traits\HasWorkScheduleHint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AddEvent extends Component
{
    use HasWorkScheduleHint;

    /**
     * Control visibility of the Add Event modal.
     * @var bool
     */
    public $showAddEventModal = false;

    public $workScheduleHint = '';

    /**
     * Control visibility of the dashboard modal.
     * @var bool
     */
    public $goDashboardModal = false;

    /**
     * Store the current date and time.
     * @var string
     */
    public $now;

    /**
     * Date for the event start.
     * @var string
     */
    public $start_date;
    public $end_date;

    /**
     * Time for the event start.
     * @var string
     */
    public $start_time;

    /**
     * User ID associated with the event.
     * @var int
     */
    public $user_id;

    /**
     * Description of the event.
     * @var string
     */
    public $description;
    public $event_type_id;
    public $eventTypes;
    public $selectedEventType;

    /**
     * Additional observations about the event.
     * @var string|null
     */
    public $observations;

    /**
     * Origin of the action triggering the event creation.
     * @var string
     */
    public $origin;

    /**
     * Event listeners.
     * @var array
     */
    protected $listeners = ['add'];

    /**
     * Validation rules for event creation.
     * @var array
     */
    protected function rules()
    {
        $rules = [
            'event_type_id' => 'required',
            'start_date' => 'required|date',
            'observations' => 'nullable|string|max:255',
        ];

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        } else if ($this->selectedEventType) {
            $rules['start_time'] = 'required';
        }

        return $rules;
    }

    /**
     * Validate individual properties on update.
     * @param string $propertyName
     * @return void
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedEventTypeId($value)
    {
        $this->selectedEventType = EventType::find($value);
    }

    /**
     * Initialize component properties.
     * @return void
     */
    public function mount()
    {
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->description = __('Workday');
        $this->observations = '';
        $this->eventTypes = collect();
        $this->event_type_id = null;
        $this->selectedEventType = null;

        // Hint is loaded for the initially authenticated user (if any)
        if (Auth::check()) {
            $this->setWorkScheduleHint();
        }
    }

    /**
     * Open the Add Event modal.
     * @param string $origin
     * @return void
     */
    public function add($data)
    {
        // Reset and fetch fresh data each time the modal is opened
        $this->reset(['description', 'observations', 'event_type_id', 'selectedEventType']);
        if (isset($data['date'])) {
            $this->start_date = \Carbon\Carbon::parse($data['date'])->format('Y-m-d');
            $this->end_date = \Carbon\Carbon::parse($data['date'])->format('Y-m-d');
            $this->start_time = \Carbon\Carbon::parse($data['date'])->format('H:i:s');
        } else {
            $this->start_date = date('Y-m-d');
            $this->end_date = date('Y-m-d');
            $this->start_time = date('H:i:s');
        }
        $this->description = __('Workday');

        if (Auth::check() && Auth::user()->currentTeam) {
            $this->eventTypes = Auth::user()->currentTeam->eventTypes;
            if ($this->eventTypes->count() > 0) {
                $this->event_type_id = $this->eventTypes->first()->id;
                $this->selectedEventType = $this->eventTypes->first();
            }
        } else {
            $this->eventTypes = collect();
        }

        $this->setWorkScheduleHint();
        $this->origin = is_array($data) ? $data['origin'] : $data;
        $this->showAddEventModal = true;
    }

    /**
     * Cancel event creation and redirect to events page.
     * @return void
     */
    public function cancel()
    {
        $this->showAddEventModal = false;
        if ($this->origin !== 'calendar') {
            $this->redirect('/events');
        }
    }

    /**
     * Save the event to the database.
     * @return mixed
     */
    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::user()->id,
            'description' => $this->selectedEventType->name,
            'observations' => $this->observations,
            'event_type_id' => $this->event_type_id,
            'is_open' => true, // All events are now created as open
            'is_authorized' => false, // Explicitly set to false for all new events
        ];

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            $data['start'] = $this->start_date . ' 00:00:00';
            $data['end'] = Carbon::parse($this->end_date)->addDay()->format('Y-m-d H:i:s');
        } else {
            $data['start'] = $this->start_date . ' ' . $this->start_time;
            $data['end'] = null;
        }

        // Ensure is_authorized is always set, defaulting to false.
        if (Schema::hasColumn('events', 'is_authorized')) {
            $data['is_authorized'] = false;
        }

        // Ensure is_authorized is always set, defaulting to false.
        if (Schema::hasColumn('events', 'is_authorized')) {
            $data['is_authorized'] = false;
        }

        $event = Event::create($data);

        // Notify team admins if a full-day event is created that needs authorization
        if ($event->eventType && $event->eventType->is_all_day) {
            $team = $event->user->currentTeam;

            if ($team) {
                $admins = $team->allUsers()->filter(function ($user) use ($team) {
                    return $user->hasTeamRole($team, 'admin');
                });

                if ($admins && $admins->isNotEmpty()) {
                    Notification::send($admins, new EventCreated($event));
                }
            }
        }

        $this->reset([
            'showAddEventModal',
        ]);

        if ($this->origin == 'numpad') {
            return redirect()->route('events')->with('info', 'E_SUCCESS');
        } else {
            $this->emitTo('get-time-registers', 'render');
            $this->emit('refreshCalendar');
        }
    }

    /**
     * Render the Livewire component view.
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.events.add-event');
    }
}
