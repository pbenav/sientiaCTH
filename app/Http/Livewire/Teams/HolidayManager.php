<?php

namespace App\Http\Livewire\Teams;

use App\Models\Holiday;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class HolidayManager extends Component
{
    public $team;
    public $isTeamAdmin;
    public $holidays;

    public $managingHoliday = false;
    public $confirmingHolidayDeletion = false;
    public $holidayId;

    // Form properties
    public $name;
    public $date;
    public $type;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'nullable|string|max:255',
        ];
    }

    public function mount($team)
    {
        $this->team = $team;
        $this->isTeamAdmin = auth()->user()->isTeamAdmin();
    }

    public function render()
    {
        $this->holidays = $this->team->holidays()->orderBy('date')->get();
        return view('livewire.teams.holiday-manager');
    }

    public function manageHoliday($holidayId = null)
    {
        $this->resetErrorBag();
        $this->managingHoliday = true;
        $this->holidayId = $holidayId;

        if ($holidayId) {
            $holiday = Holiday::find($holidayId);
            $this->name = $holiday->name;
            $this->date = $holiday->date->format('Y-m-d');
            $this->type = $holiday->type;
        } else {
            $this->name = '';
            $this->date = '';
            $this->type = '';
        }
    }

    public function saveHoliday()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'date' => $this->date,
            'type' => $this->type,
        ];

        if ($this->holidayId) {
            $holiday = Holiday::find($this->holidayId);
            Gate::forUser(auth()->user())->authorize('update', $holiday);
            $holiday->update($data);
        } else {
            Gate::forUser(auth()->user())->authorize('create', [Holiday::class, $this->team]);
            $this->team->holidays()->create($data);
        }

        $this->managingHoliday = false;
    }

    public function confirmHolidayDeletion(Holiday $holiday)
    {
        $this->confirmingHolidayDeletion = true;
        $this->holidayId = $holiday->id;
    }

    public function deleteHoliday()
    {
        $holiday = Holiday::find($this->holidayId);
        Gate::forUser(auth()->user())->authorize('delete', $holiday);
        $holiday->delete();
        $this->confirmingHolidayDeletion = false;
    }
}
