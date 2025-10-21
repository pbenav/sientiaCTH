<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\EventType;
use App\Models\ExceptionalClockInToken;
use App\Models\Message;
use App\Notifications\EventCreated;
use App\Notifications\NewMessage;
use App\Traits\HasWorkScheduleHint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AddEvent extends Component
{
    use HasWorkScheduleHint;

    public $showAddEventModal = false;
    public $workScheduleHint = '';
    public $goDashboardModal = false;
    public $now;
    public $start_date;
    public $end_date;
    public $start_time;
    public $user_id;
    public $description;
    public $event_type_id;
    public $eventTypes;
    public $selectedEventType;
    public $observations;
    public $origin;
    protected $listeners = ['add'];

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

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatedEventTypeId($value)
    {
        $this->selectedEventType = EventType::find($value);
    }

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

        if (Auth::check()) {
            $this->setWorkScheduleHint();
        }
    }

    public function add($data)
    {
        $this->reset(['description', 'observations', 'event_type_id', 'selectedEventType']);
        if (isset($data['date'])) {
            $date = \Carbon\Carbon::parse($data['date']);
            $this->start_date = $date->format('Y-m-d');
            $this->end_date = $date->format('Y-m-d');
            $this->start_time = $date->format('H:i:s');
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

    public function cancel()
    {
        $this->showAddEventModal = false;
        if ($this->origin !== 'calendar') {
            $this->redirect('/events');
        }
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;
        $eventType = $this->selectedEventType; // Correct initialization

        if ($eventType && $eventType->is_workday_type && $team && $team->force_clock_in_delay) {
            $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
            $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];
            $clockInTime = Carbon::parse($this->start_date . ' ' . $this->start_time);

            $isWithinSchedule = $this->isWithinSchedule($schedule, $clockInTime, $team->clock_in_delay_minutes ?? 0);

            if (!$isWithinSchedule) {
                $placeholderEvent = $this->createEvent(false, true); // is_exceptional = true
                $this->triggerExceptionalFlow($user, $team, $clockInTime, $placeholderEvent->id);
                return;
            }
        }

        $event = $this->createEvent();

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

        $this->reset(['showAddEventModal']);

        if ($this->origin == 'numpad') {
            return redirect()->route('events')->with('info', 'E_SUCCESS');
        } else {
            $this->emitTo('get-time-registers', 'render');
            $this->emit('refreshCalendar');
        }
    }

    public function render()
    {
        return view('livewire.events.add-event');
    }
}
