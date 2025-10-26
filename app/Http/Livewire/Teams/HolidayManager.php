<?php

namespace App\Http\Livewire\Teams;

use App\Models\Holiday;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * A Livewire component for managing holidays for a team.
 *
 * This component provides functionality for creating, updating, and deleting
 * holidays.
 */
class HolidayManager extends Component
{
    public $team;
    public bool $isTeamAdmin;
    public $holidays;

    public bool $managingHoliday = false;
    public bool $confirmingHolidayDeletion = false;
    public ?int $holidayId;

    // Form properties
    public string $name;
    public string $date;
    public ?string $type;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'nullable|string|max:255',
        ];
    }

    /**
     * Mount the component.
     *
     * @param mixed $team
     * @return void
     */
    public function mount($team): void
    {
        $this->team = $team;
        $this->isTeamAdmin = auth()->user()->isTeamAdmin();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->holidays = $this->team->holidays()->orderBy('date')->get();
        return view('livewire.teams.holiday-manager');
    }

    /**
     * Show the form for managing a holiday.
     *
     * @param int|null $holidayId
     * @return void
     */
    public function manageHoliday(int $holidayId = null): void
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

    /**
     * Save the holiday.
     *
     * @return void
     */
    public function saveHoliday(): void
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

    /**
     * Confirm the deletion of a holiday.
     *
     * @param \App\Models\Holiday $holiday
     * @return void
     */
    public function confirmHolidayDeletion(Holiday $holiday): void
    {
        $this->confirmingHolidayDeletion = true;
        $this->holidayId = $holiday->id;
    }

    /**
     * Delete a holiday.
     *
     * @return void
     */
    public function deleteHoliday(): void
    {
        $holiday = Holiday::find($this->holidayId);
        Gate::forUser(auth()->user())->authorize('delete', $holiday);
        $holiday->delete();
        $this->confirmingHolidayDeletion = false;
    }
}
