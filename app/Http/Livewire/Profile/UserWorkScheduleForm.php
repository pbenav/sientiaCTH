<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Support\Facades\Auth;

/**
 * A Livewire component for managing a user's work schedule.
 *
 * This component provides a form for users to define their weekly work
 * schedule, including start and end times for each day.
 */
class UserWorkScheduleForm extends Component
{
    public $user;
    public array $schedule = [];

    protected $rules = [
        'schedule.*.start' => 'required|date_format:H:i',
        'schedule.*.end' => 'required|date_format:H:i',
    ];

    /**
     * Mount the component.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;
        $workSchedule = UserMeta::where('user_id', $this->user->id)
                                ->where('meta_key', 'work_schedule')
                                ->first();

        if ($workSchedule) {
            $this->schedule = json_decode($workSchedule->meta_value, true);
            
            // Normalizar datos antiguos (letras) a nuevo formato (números ISO)
            // Esto asegura que se visualicen correctamente aunque no se haya ejecutado la migración de BD
            $this->normalizeScheduleDays();
        }
    }

    /**
     * Normaliza los días del horario al formato ISO (1-7)
     */
    private function normalizeScheduleDays(): void
    {
        $dayMap = [
            'L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7
        ];
        foreach ($this->schedule as &$slot) {
            if (isset($slot['days']) && is_array($slot['days'])) {
                $newDays = [];
                foreach ($slot['days'] as $day) {
                    // Si es letra conocida, convertir a número
                    if (is_string($day) && isset($dayMap[strtoupper($day)])) {
                        $newDays[] = $dayMap[strtoupper($day)];
                    }
                    // Si ya es número o no reconocido, mantener
                    else {
                        $newDays[] = $day;
                    }
                }
                // Eliminar duplicados y ordenar ascendente (1-7)
                $newDays = array_unique($newDays);
                sort($newDays);
                $slot['days'] = $newDays;
            }
        }
    }

    /**
     * Add a new row to the schedule.
     *
     * @return void
     */
    public function addScheduleRow(): void
    {
        $this->schedule[] = ['start' => '', 'end' => '', 'days' => []];
    }

    /**
     * Remove a row from the schedule.
     *
     * @param int $index
     * @return void
     */
    public function removeScheduleRow(int $index): void
    {
        unset($this->schedule[$index]);
        $this->schedule = array_values($this->schedule);
    }

    /**
     * Save the work schedule.
     *
     * @return void
     */
    public function save(): void
    {
        $this->validate();

        UserMeta::updateOrCreate(
            ['user_id' => $this->user->id, 'meta_key' => 'work_schedule'],
            ['meta_value' => json_encode($this->schedule)]
        );

        session()->flash('message', __('Work schedule updated successfully.'));
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.profile.user-work-schedule-form');
    }
}
