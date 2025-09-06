<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Traits\HasWorkScheduleHint;
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
    protected $rules = [
        'start_date' => 'required|after:-7 day|before:+1 day', // no more than one day before
        'start_time' => 'required|after:-12 hours|before:+12 hours', // |after_or_equal:now', When needed!!!
        'description' => 'required',
        'observations' => 'string|max:255|nullable'
    ];

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
    public function add($origin)
    {
        // Reset and fetch fresh data each time the modal is opened
        $this->reset(['description', 'observations', 'event_type_id', 'selectedEventType']);
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->description = __('Workday');

        if (Auth::check() && Auth::user()->currentTeam) {
            $this->eventTypes = Auth::user()->currentTeam->eventTypes;
            if ($this->eventTypes->count() > 0) {
                $this->event_type_id = $this->eventTypes->first()->id;
            }
        } else {
            $this->eventTypes = collect();
        }

        $this->setWorkScheduleHint();
        $this->origin = $origin;
        $this->showAddEventModal = true;
    }

    /**
     * Cancel event creation and redirect to events page.
     * @return void
     */
    public function cancel()
    {
        $this->showAddEventModal = false;
        $this->redirect('/events');
    }

    /**
     * Save the event to the database.
     * @return mixed
     */
    public function save()
    {
        $this->validate();

        $start = $this->start_date;
        $end = null;

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            $start .= ' 00:00:00';
            $end = $this->end_date . ' 23:59:59';
        } else {
            $start .= ' ' . $this->start_time;
            // For non-all-day events, 'end' remains null on creation.
        }

        Event::create([
            'start' => $start,
            'end' => $end,
            'user_id' => Auth::user()->id,
            'description' => $this->selectedEventType ? $this->selectedEventType->name : $this->description,
            'observations' => $this->observations,
            'event_type_id' => $this->event_type_id,
            'is_open' => true,
            'is_authorized' => $this->selectedEventType && $this->selectedEventType->is_all_day ? false : false, // Default to not authorized for all-day events
        ]);

        $this->reset([
            'showAddEventModal',
        ]);

        if ($this->origin == 'numpad') {
            return redirect()->route('events')->with('info', 'E_SUCCESS');
        } else { 
            $this->emitTo('get-time-registers', 'render');        
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
