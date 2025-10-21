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
        $isExtraHours = false;
        $team = $user->currentTeam;

        if ($team && $team->force_clock_in_delay && $this->selectedEventType && $this->selectedEventType->is_workday_type) {
            $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
            $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

            $clockInTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
            $dayOfWeek = $clockInTime->format('N');
            $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
            $dayAbbr = $dayMap[$dayOfWeek] ?? null;

            $todaysSlots = collect($schedule)->filter(function ($slot) use ($dayAbbr) {
                return in_array($dayAbbr, $slot['days']);
            });

            if ($todaysSlots->isEmpty()) {
                $isExtraHours = true;
            } else {
                $isWithinAnySlot = false;
                $allowedDelay = $team->clock_in_delay_minutes;

                foreach ($todaysSlots as $slot) {
                    $startTime = Carbon::parse($this->start_date . ' ' . $slot['start']);
                    $endTime = Carbon::parse($this->start_date . ' ' . $slot['end']);

                    // Check start time window
                    if ($clockInTime->between($startTime->copy()->subMinutes($allowedDelay), $startTime->copy()->addMinutes($allowedDelay))) {
                        $isWithinAnySlot = true;
                        break;
                    }

                    // Check end time window
                    if ($clockInTime->between($endTime->copy()->subMinutes($allowedDelay), $endTime->copy()->addMinutes($allowedDelay))) {
                        $isWithinAnySlot = true;
                        break;
                    }
                }

                if (!$isWithinAnySlot) {
                    // Find the immediately preceding work slot
                    $previousSlot = null;
                    $minDiff = PHP_INT_MAX;

                    foreach ($todaysSlots as $slot) {
                        $startTime = Carbon::parse($this->start_date . ' ' . $slot['start']);
                        if ($startTime->isBefore($clockInTime)) {
                            $diff = $clockInTime->diffInMinutes($startTime);
                            if ($diff < $minDiff) {
                                $minDiff = $diff;
                                $previousSlot = $slot;
                            }
                        }
                    }

                    if ($previousSlot) {
                        $defaultWorkCenter = $user->meta->where('meta_key', 'default_work_center_id')->first();
                        $defaultWorkCenterId = ($defaultWorkCenter && !empty($defaultWorkCenter->meta_value)) ? $defaultWorkCenter->meta_value : null;

                        $event = Event::create([
                            'user_id' => $user->id,
                            'work_center_id' => $defaultWorkCenterId,
                            'description' => $this->selectedEventType->name,
                            'observations' => __('Created exceptionally. Please regularize.'),
                            'event_type_id' => $this->event_type_id,
                            'start' => Carbon::parse($this->start_date . ' ' . $previousSlot['start'])->setTimezone('UTC'),
                            'end' => Carbon::parse($this->start_date . ' ' . $previousSlot['end'])->setTimezone('UTC'),
                            'is_open' => false,
                            'is_authorized' => false,
                            'is_exceptional' => true,
                            'is_extra_hours' => false,
                        ]);

                        if ($this->origin === 'numpad') {
                             return redirect()->route('events', ['edit_event' => $event->id])->with('alertFail', __('exceptional_clock_in.regularize_event'));
                        } else {
                            $this->emitTo('get-time-registers', 'render');
                            $this->emit('refreshCalendar');
                            $this->emitTo('edit-event', 'edit', $event->id);
                            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => __('exceptional_clock_in.regularize_event')]);
                        }

                    } else {
                         if ($this->origin === 'numpad') {
                            return redirect()->route('events')->with('alertFail', __('exceptional_clock_in.no_previous_slot'));
                        } else {
                            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => __('exceptional_clock_in.no_previous_slot')]);
                        }
                    }

                    $this->showAddEventModal = false;
                    return;
                }
            }
        }

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

    public function render()
    {
        return view('livewire.events.add-event');
    }
}
