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
            $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
            $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

            // Use the original clock-in time from the token's data
            $tokenData = json_decode($this->tokenRecord->data, true);
            $clockInTime = Carbon::parse($tokenData['start']);

            $this->start_date = $clockInTime->format('Y-m-d');
            $this->end_date = $clockInTime->format('Y-m-d');

            $dayOfWeek = $clockInTime->format('N');
            $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
            $dayAbbr = $dayMap[$dayOfWeek] ?? null;

            $todaysSlots = collect($schedule)->filter(function ($slot) use ($dayAbbr) {
                return !empty($slot['days']) && in_array($dayAbbr, $slot['days']);
            });

            // Find the closest slot
            $closestSlot = null;
            $minDiff = PHP_INT_MAX;

            foreach ($todaysSlots as $slot) {
                $slotStart = Carbon::parse($clockInTime->format('Y-m-d') . ' ' . $slot['start']);
                $diff = abs($clockInTime->getTimestamp() - $slotStart->getTimestamp());
                if ($diff < $minDiff) {
                    $minDiff = $diff;
                    $closestSlot = $slot;
                }
            }

            if ($closestSlot) {
                $this->start_time = Carbon::parse($closestSlot['start'])->format('H:i');
                $this->end_time = Carbon::parse($closestSlot['end'])->format('H:i');
            } else {
                // Default if no schedule found for the day
                $this->start_time = $clockInTime->format('H:i');
                $this->end_time = $clockInTime->copy()->addHours(8)->format('H:i');
            }

            $this->observations = __('Eg: I forgot to clock in when I arrived.');

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

        $event = Event::find($this->tokenRecord->event_id);

        if ($event) {
            $event->update([
                'observations' => $this->observations,
                'start' => Carbon::parse($this->start_date . ' ' . $this->start_time, config('app.timezone'))->setTimezone('UTC'),
                'end' => Carbon::parse($this->end_date . ' ' . $this->end_time, config('app.timezone'))->setTimezone('UTC'),
                'is_open' => false,
                'is_exceptional' => false, // Mark as regularized
                'is_authorized' => false, // Needs authorization after regularization
            ]);
        }

        $this->tokenRecord->update(['used_at' => now()]);

        session()->flash('success', __('exceptional_clock_in.success'));

        return redirect()->route('events');
    }

    public function render()
    {
        return view('livewire.events.exceptional-clock-in');
    }
}
