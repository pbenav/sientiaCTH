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

        $this->setSuggestedScheduleInfo();

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

    private function setSuggestedScheduleInfo()
    {
        if (!Auth::check()) {
            $this->workScheduleHint = '';
            return;
        }

        $user = Auth::user();
        $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();

        if (!$workScheduleMeta) {
            $this->workScheduleHint = 'No hay horario laboral definido.';
            return;
        }

        $schedule = json_decode($workScheduleMeta->meta_value, true);
        $now = new \DateTime();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->format('N'); // 1 (Monday) to 7 (Sunday)
        $dayMap = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];

        $todaysSlots = [];
        foreach ($schedule as $slot) {
            $slotDays = $slot['days'] ?? [];
            foreach ($slotDays as $day) {
                if (isset($dayMap[$day]) && $dayMap[$day] == $currentDay) {
                    $todaysSlots[] = $slot;
                    break;
                }
            }
        }

        if (empty($todaysSlots)) {
            $this->workScheduleHint = 'No hay tramos para hoy.';
            return;
        }

        // Sort today's slots by start time
        usort($todaysSlots, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $currentSlot = null;
        $lastFinishedSlot = null;

        foreach ($todaysSlots as $slot) {
            if ($currentTime >= $slot['start'] && $currentTime <= $slot['end']) {
                $currentSlot = $slot;
                break; // Found the current slot
            }
            if ($currentTime > $slot['end']) {
                $lastFinishedSlot = $slot; // This might be the one we are looking for
            }
        }

        $relevantSlot = $currentSlot ?? $lastFinishedSlot;

        if ($relevantSlot) {
            if ($currentSlot) {
                $this->workScheduleHint = "Tramo actual sugerido: {$relevantSlot['start']} - {$relevantSlot['end']}";
            } else {
                $this->workScheduleHint = "Último tramo finalizado: {$relevantSlot['start']} - {$relevantSlot['end']}";
            }

            if ($this->event->is_open == 1) {
                $this->event->end = date('Y-m-d') . ' ' . $relevantSlot['end'];
            }
        } else {
            $this->workScheduleHint = 'No se encontró un tramo horario aplicable.';
        }
    }
}
