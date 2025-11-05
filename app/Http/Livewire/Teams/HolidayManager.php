<?php

namespace App\Http\Livewire\Teams;

use App\Models\Holiday;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HolidayManager extends Component
{
    public $team;
    public bool $isTeamAdmin;
    public $holidays;

    public bool $managingHoliday = false;
    public bool $confirmingHolidayDeletion = false;
    public ?int $holidayId = null;

    // Form state
    public array $holidayForm = [
        'name' => '',
        'date' => null,
        'type' => null,
    ];

    protected function rules(): array
    {
        return [
            'holidayForm.name' => 'required|string|max:255',
            'holidayForm.date' => 'required|date',
            'holidayForm.type' => 'nullable|string|max:255',
        ];
    }

    public function mount($team): void
    {
        $this->team = $team;
        $this->isTeamAdmin = method_exists(auth()->user(), 'isTeamAdmin') ? auth()->user()->isTeamAdmin($team) : false;
        $this->loadHolidays();
        
        // Pre-rellenar fecha con hoy
        $this->holidayForm['date'] = now()->format('Y-m-d');
    }

    protected function loadHolidays(): void
    {
        // Assumes team->holidays() relationship
        $this->holidays = $this->team->holidays()->orderBy('date')->get();
    }

    // Alias/compatibility with views that call managingHoliday(...) as a method
    public function managingHoliday($flag = true): void
    {
        $this->managingHoliday = (bool) $flag;
        if ($this->managingHoliday === false) {
            $this->resetForm();
        }
    }

    protected function resetForm(): void
    {
        $this->holidayId = null;
        $this->holidayForm = [
            'name' => '', 
            'date' => now()->format('Y-m-d'), 
            'type' => null
        ];
    }

    public function editHoliday(int $id): void
    {
        $holiday = Holiday::findOrFail($id);
        
        // Verify authorization
        if (!Gate::allows('update', $holiday)) {
            abort(403, __('Unauthorized action'));
        }
        
        $this->holidayId = $holiday->id;
        $this->holidayForm = [
            'name' => $holiday->name,
            'date' => $holiday->date ? $holiday->date->format('Y-m-d') : now()->format('Y-m-d'),
            'type' => $holiday->type,
        ];
        $this->managingHoliday = true;
    }

    public function confirmHolidayDeletion(int $id): void
    {
        $this->holidayId = $id;
        $this->confirmingHolidayDeletion = true;
    }

    public function saveHoliday(): void
    {
        $this->validate();

        if ($this->holidayId) {
            $h = Holiday::findOrFail($this->holidayId);
            
            // Verify authorization to update
            if (!Gate::allows('update', $h)) {
                abort(403, __('Unauthorized action'));
            }
            
            $h->update([
                'name' => $this->holidayForm['name'],
                'date' => $this->holidayForm['date'],
                'type' => $this->holidayForm['type'],
            ]);
            session()->flash('success', __('Holiday updated.'));
        } else {
            // Verify authorization to create
            if (!Gate::allows('create', [Holiday::class, $this->team])) {
                abort(403, __('Unauthorized action'));
            }
            
            $this->team->holidays()->create([
                'name' => $this->holidayForm['name'],
                'date' => $this->holidayForm['date'],
                'type' => $this->holidayForm['type'],
            ]);
            session()->flash('success', __('Holiday created.'));
        }

        $this->managingHoliday = false;
        $this->resetForm();
        $this->loadHolidays();
    }

    public function deleteHoliday(): void
    {
        if ($this->holidayId) {
            $h = Holiday::find($this->holidayId);
            if ($h) {
                // Verify authorization to delete
                if (!Gate::allows('delete', $h)) {
                    abort(403, __('Unauthorized action'));
                }
                
                $h->delete();
                session()->flash('success', __('Holiday deleted.'));
            }
        }
        $this->confirmingHolidayDeletion = false;
        $this->holidayId = null;
        $this->loadHolidays();
    }

    public function render()
    {
        return view('livewire.teams.holiday-manager', [
            'team' => $this->team,
        ]);
    }
}