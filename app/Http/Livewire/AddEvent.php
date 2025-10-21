<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\EventType;
use App\Traits\HasWorkScheduleHint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class AddEvent extends Component
{
    use HasWorkScheduleHint;

    public $showAddEventModal = false;
    public $workScheduleHint = '';
    public $start_date;
    public $end_date;
    public $start_time;
    public $event_type_id;
    public $eventTypes;
    public $selectedEventType;
    public $observations;
    public $origin;
    protected $listeners = ['add'];

    protected function rules()
    {
        return [
            'event_type_id' => 'required',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'observations' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        $this->start_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
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
        $this->reset(['observations', 'event_type_id', 'selectedEventType']);

        $date = isset($data['date']) ? Carbon::parse($data['date']) : Carbon::now();
        $this->start_date = $date->format('Y-m-d');
        $this->start_time = $date->format('H:i:s');

        if (Auth::check() && Auth::user()->currentTeam) {
            $this->eventTypes = Auth::user()->currentTeam->eventTypes()->where('is_workday_type', true)->get();
            if ($this->eventTypes->count() > 0) {
                $defaultEventType = $this->eventTypes->first();
                $this->event_type_id = $defaultEventType->id;
                $this->selectedEventType = $defaultEventType;
            }
        }

        $this->setWorkScheduleHint();
        $this->origin = is_array($data) ? $data['origin'] : $data;
        $this->showAddEventModal = true;
    }

    public function cancel()
    {
        $this->showAddEventModal = false;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;
        $clockInTime = Carbon::parse($this->start_date . ' ' . $this->start_time);

        $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
        $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

        $isExceptionalDate = !$clockInTime->isToday();
        list($isWithinSlot, $closestSlot) = $this->findClosestSlot($clockInTime, $schedule, $team->clock_in_delay_minutes ?? 15);

        $isExceptional = $isExceptionalDate || !$isWithinSlot;

        if ($isExceptional) {
            if (!$closestSlot) {
                $this->dispatchBrowserEvent('alertFail', ['message' => __('No se encontró un tramo horario cercano para crear el evento excepcional.')]);
                return;
            }

            $slotStart = Carbon::parse($clockInTime->format('Y-m-d') . ' ' . $closestSlot['start']);
            $slotEnd = Carbon::parse($clockInTime->format('Y-m-d') . ' ' . $closestSlot['end']);

            Event::create([
                'user_id' => $user->id,
                'work_center_id' => $user->meta->where('meta_key', 'default_work_center_id')->first()->meta_value ?? null,
                'description' => $this->selectedEventType->name,
                'observations' => __('Creado de forma excepcional: ') . $this->observations,
                'event_type_id' => $this->event_type_id,
                'start' => $slotStart,
                'end' => $slotEnd,
                'is_open' => false,
                'is_confirmed' => true,
                'is_exceptional' => true,
                'is_authorized' => false,
            ]);

            session()->flash('info', 'Se ha creado un evento excepcional confirmado. Por favor, regularícelo si es necesario.');

        } else {
            // Standard event creation
            Event::create([
                'user_id' => $user->id,
                'work_center_id' => $user->meta->where('meta_key', 'default_work_center_id')->first()->meta_value ?? null,
                'description' => $this->selectedEventType->name,
                'observations' => $this->observations,
                'event_type_id' => $this->event_type_id,
                'start' => $clockInTime,
                'end' => null,
                'is_open' => true,
                'is_confirmed' => false,
                'is_exceptional' => false,
                'is_authorized' => false,
            ]);
        }

        $this->showAddEventModal = false;
        $this->emit('refreshCalendar');
        $this->emitTo('get-time-registers', 'render');
    }

    private function findClosestSlot(Carbon $time, array $schedule, int $allowedDelay)
    {
        $dayOfWeek = $time->format('N');
        $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
        $dayAbbr = $dayMap[$dayOfWeek] ?? null;

        $todaysSlots = collect($schedule)->filter(fn($slot) => in_array($dayAbbr, $slot['days']));

        if ($todaysSlots->isEmpty()) {
            return [false, null];
        }

        $closestSlot = null;
        $minDiff = PHP_INT_MAX;
        $isWithinAnySlot = false;

        foreach ($todaysSlots as $slot) {
            $slotStart = Carbon::parse($time->format('Y-m-d') . ' ' . $slot['start']);
            $slotEnd = Carbon::parse($time->format('Y-m-d') . ' ' . $slot['end']);

            if ($time->between($slotStart->copy()->subMinutes($allowedDelay), $slotStart->copy()->addMinutes($allowedDelay)) ||
                $time->between($slotEnd->copy()->subMinutes($allowedDelay), $slotEnd->copy()->addMinutes($allowedDelay))) {
                $isWithinAnySlot = true;
            }

            $diff = abs($time->diffInMinutes($slotStart));
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestSlot = $slot;
            }
        }

        return [$isWithinAnySlot, $closestSlot];
    }

    public function render()
    {
        return view('livewire.events.add-event');
    }
}
