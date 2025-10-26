<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\EventType;
use App\Models\ExceptionalClockInToken;
use App\Models\Message;
use App\Notifications\EventCreated;
use App\Notifications\NewMessage;
use App\Traits\HasWorkScheduleHint;
use App\Traits\HandlesEventAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * A Livewire component for adding new events.
 *
 * This component provides a modal form for creating new events, including
 * handling for exceptional clock-ins and different event types.
 */
class AddEvent extends Component
{
    use HasWorkScheduleHint;
    use HandlesEventAuthorization;

    public bool $showAddEventModal = false;
    public string $workScheduleHint = '';
    public bool $goDashboardModal = false;
    public $now;
    public string $start_date;
    public string $end_date;
    public string $start_time;
    public int $user_id;
    public string $description;
    public int $event_type_id;
    public $eventTypes;
    public ?EventType $selectedEventType;
    public string $observations;
    public string $origin;
    protected $listeners = ['add'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
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
     * Handle the update of the event_type_id property.
     *
     * @param int $value
     * @return void
     */
    public function updatedEventTypeId(int $value): void
    {
        $this->selectedEventType = EventType::find($value);
    }

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function mount(): void
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

    /**
     * Show the add event modal.
     *
     * @param array|string $data
     * @return void
     */
    public function add($data): void
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

    /**
     * Close the add event modal.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->showAddEventModal = false;
        if ($this->origin !== 'calendar') {
            $this->redirect('/events');
        }
    }

    /**
     * Save the new event.
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;
        $appTimezone = config('app.timezone');

        $eventStartTime = Carbon::parse($this->start_date . ' ' . $this->start_time, $appTimezone);

        // Use the trait to check if the user is within their work schedule
        if ($team && $team->force_clock_in_delay && $this->selectedEventType && $this->selectedEventType->is_workday_type && !$this->isWithinWorkSchedule($eventStartTime)) {
            // If outside the schedule, trigger the exceptional clock-in flow
            $token = Str::random(60);
            ExceptionalClockInToken::create([
                'user_id' => $user->id,
                'team_id' => $team->id,
                'token' => $token,
                'expires_at' => now()->addMinutes($team->clock_in_grace_period_minutes ?? 10),
            ]);

            $adminSender = $team->owner;
            $url = route('exceptional.clock-in.form', ['token' => $token]);
            $messageContent = __('exceptional_clock_in.message_content', [
                'minutes' => $team->clock_in_grace_period_minutes ?? 10,
                'url' => $url
            ]);

            $message = Message::create([
                'sender_id' => $adminSender->id,
                'subject' => __('exceptional_clock_in.message_subject'),
                'body' => $messageContent,
                'is_log' => true,
            ]);

            $message->recipients()->attach($user->id);
            $user->notify(new NewMessage($message));

            if ($this->origin === 'numpad') {
                $this->showAddEventModal = false;
                return redirect()->route('events')->with('alertFail', __('exceptional_clock_in.validation_error'));
            } else {
                $this->dispatchBrowserEvent('alertFail', ['message' => __('exceptional_clock_in.validation_error')]);
                $this->showAddEventModal = false;
                $this->emit('refreshCalendar');
            }
            return;
        }

        $isExtraHours = !$this->isWithinWorkSchedule($eventStartTime);

        $defaultWorkCenter = $user->meta->where('meta_key', 'default_work_center_id')->first();
        $defaultWorkCenterId = ($defaultWorkCenter && !empty($defaultWorkCenter->meta_value)) ? $defaultWorkCenter->meta_value : null;

        $data = [
            'user_id' => Auth::user()->id,
            'work_center_id' => $defaultWorkCenterId,
            'description' => $this->selectedEventType->name,
            'observations' => $this->observations,
            'event_type_id' => $this->event_type_id,
            'is_open' => true,
            'is_authorized' => false,
            'is_extra_hours' => $isExtraHours,
        ];

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            $data['start'] = Carbon::parse($this->start_date, $appTimezone)->startOfDay()->setTimezone('UTC');
            $data['end'] = Carbon::parse($this->end_date, $appTimezone)->startOfDay()->addDay()->setTimezone('UTC');
        } else {
            $data['start'] = Carbon::parse($this->start_date . ' ' . $this->start_time, $appTimezone)->setTimezone('UTC');
            $data['end'] = null;
        }

        if (Schema::hasColumn('events', 'is_authorized')) {
            $data['is_authorized'] = false;
        }

        $event = Event::create($data);

        if ($isExtraHours) {
            session()->flash('info', 'El evento se ha registrado como horas extra al no encontrarse en un tramo horario definido.');
        }

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

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.events.add-event');
    }
}
