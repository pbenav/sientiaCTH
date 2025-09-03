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

    /**
     * Initialize component properties.
     * @return void
     */
    public function mount()
    {
        $this->start_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->description = __('Workday');
        $this->observations = '';
        $this->getWorkScheduleHint();
    }


    /**
     * Open the Add Event modal.
     * @param string $origin
     * @return void
     */
    public function add($origin)
    {
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

        Event::create([
            'start' => $this->start_date . ' ' . $this->start_time,
            'end' => null,
            'user_id' => Auth::user()->id,
            'user_code' => Auth::user()->user_code,
            'description' => $this->description,
            'observations' => $this->observations,
            'is_open' => true
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
