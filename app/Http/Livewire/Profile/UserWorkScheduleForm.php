<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Support\Facades\Auth;

class UserWorkScheduleForm extends Component
{
    public User $user;
    public $schedule = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $workSchedule = UserMeta::where('user_id', $this->user->id)
                                ->where('meta_key', 'work_schedule')
                                ->first();

        if ($workSchedule) {
            $this->schedule = json_decode($workSchedule->meta_value, true);
        }
    }

    public function addScheduleRow()
    {
        $this->schedule[] = ['start' => '', 'end' => '', 'days' => []];
    }

    public function removeScheduleRow($index)
    {
        unset($this->schedule[$index]);
        $this->schedule = array_values($this->schedule);
    }

    public function save()
    {
        UserMeta::updateOrCreate(
            ['user_id' => $this->user->id, 'meta_key' => 'work_schedule'],
            ['meta_value' => json_encode($this->schedule)]
        );

        session()->flash('message', 'Horario laboral actualizado con éxito.');
    }

    public function render()
    {
        return view('livewire.profile.user-work-schedule-form');
    }
}
