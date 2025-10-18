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

        $user = Auth::user();
        $team = $user->currentTeam;

        if ($team && $team->force_clock_in_delay && $this->selectedEventType && $this->selectedEventType->is_workday_type) {
            $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
            $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

            $clockInTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
            $dayOfWeek = $clockInTime->format('N'); // 1 (Lunes) a 7 (Domingo)
            $dayMap = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];
            $dayAbbr = array_search($dayOfWeek, $dayMap);

            $todaysSlots = collect($schedule)->filter(function ($slot) use ($dayAbbr) {
                return in_array($dayAbbr, $slot['days']);
            });

            if ($todaysSlots->isNotEmpty()) {
                $relevantSlot = null;
                $minDiff = PHP_INT_MAX;

                foreach ($todaysSlots as $slot) {
                    $startTime = Carbon::parse($this->start_date . ' ' . $slot['start']);
                    $endTime = Carbon::parse($this->start_date . ' ' . $slot['end']);

                    $diffToStart = abs($clockInTime->getTimestamp() - $startTime->getTimestamp());
                    $diffToEnd = abs($clockInTime->getTimestamp() - $endTime->getTimestamp());

                    if ($diffToStart < $minDiff) {
                        $minDiff = $diffToStart;
                        $relevantSlot = ['time' => $startTime, 'type' => 'start'];
                    }
                    if ($diffToEnd < $minDiff) {
                        $minDiff = $diffToEnd;
                        $relevantSlot = ['time' => $endTime, 'type' => 'end'];
                    }
                }

                if ($relevantSlot) {
                    $allowedDelay = $team->clock_in_delay_minutes;
                    $scheduledTime = $relevantSlot['time'];

                    if ($clockInTime->diffInMinutes($scheduledTime) > $allowedDelay) {
                        $token = Str::random(60);
                        ExceptionalClockInToken::create([
                            'user_id' => $user->id,
                            'team_id' => $team->id,
                            'token' => $token,
                            'expires_at' => now()->addMinutes($team->clock_in_grace_period_minutes),
                        ]);

                        // Send a message to the user with the exceptional clock-in link
                        $adminSender = $team->owner; // Or find another admin/system user
                        $url = route('exceptional.clock-in', ['token' => $token]);
                        $messageContent = __('exceptional_clock_in.message_content', [
                            'minutes' => $team->clock_in_grace_period_minutes,
                             'url' => $url
                        ]);

                        $message = Message::create([
                            'sender_id' => $adminSender->id,
                            'content' => $messageContent,
                            'is_log' => true,
                        ]);

                        $message->recipients()->attach($user->id);

                        // Notify the user about the new message
                        $user->notify(new NewMessage($message));


                        throw ValidationException::withMessages([
                            'start_time' => __('exceptional_clock_in.validation_error'),
                        ]);
                    }
                }
            }
        }


        $defaultWorkCenter = $user->meta->where('meta_key', 'default_work_center_id')->first();
        $defaultWorkCenterId = $defaultWorkCenter ? $defaultWorkCenter->meta_value : null;

        $data = [
            'user_id' => Auth::user()->id,
            'work_center_id' => $defaultWorkCenterId,
            'description' => $this->selectedEventType->name,
            'observations' => $this->observations,
            'event_type_id' => $this->event_type_id,
            'is_open' => true, // All events are now created as open
            'is_authorized' => false, // Explicitly set to false for all new events
        ];

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            $data['start'] = Carbon::parse($this->start_date, config('app.timezone'))
                ->startOfDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $data['end'] = Carbon::parse($this->end_date, config('app.timezone'))
                ->startOfDay()
                ->addDay()
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        } else {
            $data['start'] = Carbon::parse($this->start_date . ' ' . $this->start_time, config('app.timezone'))
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
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
