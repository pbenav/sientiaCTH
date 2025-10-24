<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\ExceptionalClockInToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExceptionalClockIn extends Component
{
    public $token;
    public $start_date;
    public $start_time;
    public $end_date;
    public $end_time;
    public $observations;
    public $tokenRecord;
    public $isValidToken = false;

    protected function rules()
    {
        return [
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'observations' => 'required|string|max:255',
        ];
    }

    public function mount($token)
    {
        $this->token = $token;
        $this->tokenRecord = ExceptionalClockInToken::where('token', $token)->first();

        if ($this->tokenRecord && !$this->tokenRecord->used_at && Carbon::now()->isBefore($this->tokenRecord->expires_at)) {
            $this->isValidToken = true;

            $user = User::find($this->tokenRecord->user_id);
            $workScheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
            $precedingSlot = null;

            if ($workScheduleMeta) {
                $workSchedule = json_decode($workScheduleMeta->meta_value, true);
                $now = Carbon::now();
                $dayOfWeek = $now->format('N');
                $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
                $currentDayLetter = $dayMap[$dayOfWeek];
                $latestPrecedingTime = null;

                if(is_array($workSchedule)) {
                    foreach ($workSchedule as $slot) {
                        if (isset($slot['days']) && in_array($currentDayLetter, $slot['days']) && isset($slot['start']) && isset($slot['end'])) {
                            $endTime = Carbon::parse($slot['end']);
                            if ($endTime->isBefore($now)) {
                                if (is_null($latestPrecedingTime) || $endTime->isAfter($latestPrecedingTime)) {
                                    $latestPrecedingTime = $endTime;
                                    $precedingSlot = $slot;
                                }
                            }
                        }
                    }
                }
            }

            if ($precedingSlot) {
                $this->start_date = now()->format('Y-m-d');
                $this->start_time = Carbon::parse($precedingSlot['start'])->format('H:i');
                $this->end_date = now()->format('Y-m-d');
                $this->end_time = Carbon::parse($precedingSlot['end'])->format('H:i');
            } else {
                 // Fallback to current time if no preceding slot is found
                $this->start_date = now()->format('Y-m-d');
                $this->start_time = now()->format('H:i');
                $this->end_date = now()->format('Y-m-d');
                $this->end_time = now()->addMinutes(1)->format('H:i');
            }

            $this->observations = __('Provide a reason for the regularization. E.g.: Forgot to clock in');
        } else {
            session()->flash('error', __('exceptional_clock_in.invalid_link'));
        }
    }

    public function save()
    {
        $this->validate();

        if (!$this->isValidToken) {
            return;
        }

        $user = User::find($this->tokenRecord->user_id);
        $team = $this->tokenRecord->team;
        $workdayEventType = $team->eventTypes()->where('is_workday_type', true)->first();

        if (!$workdayEventType) {
             session()->flash('error', __('exceptional_clock_in.no_workday_event_type'));
             return;
        }

        $defaultWorkCenter = $user->meta->where('meta_key', 'default_work_center_id')->first();
        $defaultWorkCenterId = ($defaultWorkCenter && !empty($defaultWorkCenter->meta_value)) ? $defaultWorkCenter->meta_value : null;

        Event::create([
            'user_id' => $user->id,
            'work_center_id' => $defaultWorkCenterId,
            'description' => $workdayEventType->name,
            'observations' => $this->observations,
            'event_type_id' => $workdayEventType->id,
            'start' => Carbon::parse($this->start_date . ' ' . $this->start_time, config('app.timezone'))->setTimezone('UTC'),
            'end' => Carbon::parse($this->end_date . ' ' . $this->end_time, config('app.timezone'))->setTimezone('UTC'),
            'is_open' => false,
            'is_authorized' => false,
            'is_exceptional' => true,
        ]);

        $this->tokenRecord->update(['used_at' => now()]);

        session()->flash('success', __('exceptional_clock_in.success'));

        return redirect()->route('events');
    }

    public function render()
    {
        return view('livewire.events.exceptional-clock-in');
    }
}
