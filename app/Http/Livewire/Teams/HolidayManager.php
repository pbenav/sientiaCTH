<?php

namespace App\Http\Livewire\Teams;

use App\Models\Holiday;
use App\Services\HolidayApiService;
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
    public bool $importingHolidays = false;
    public ?int $holidayId = null;
    public int $importYear;
    public array $availableHolidays = [];
    public array $selectedHolidays = [];

    // Form state
    public array $holidayForm = [
        'name' => '',
        'date' => null,
        'type' => '',
    ];

    protected function rules(): array
    {
        return [
            'holidayForm.name' => 'required|string|max:255',
            'holidayForm.date' => 'required|date',
            'holidayForm.type' => 'required|string|in:Nacional,Regional,Local,Otros',
        ];
    }

    public function mount($team): void
    {
        $this->team = $team;
        $this->isTeamAdmin = method_exists(auth()->user(), 'isTeamAdmin') ? auth()->user()->isTeamAdmin($team) : false;
        $this->loadHolidays();
        
        // Pre-rellenar fecha con hoy
        $this->holidayForm['date'] = now()->format('Y-m-d');
        
        // Initialize import year with current year
        $this->importYear = now()->year;
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
            'type' => ''
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

    public function openImportHolidays(): void
    {
        $this->importingHolidays = true;
        $this->loadAvailableHolidays();
    }

    public function loadAvailableHolidays(): void
    {
        $holidayService = app(HolidayApiService::class);
        
        // Get municipality from work centers
        $municipality = $this->getMunicipalityFromWorkCenters();
        
        // Fetch holidays from API
        $this->availableHolidays = $holidayService->fetchHolidays($this->importYear, $municipality);
        
        // Reset selected holidays
        $this->selectedHolidays = [];
        
        // Filter out holidays that already exist
        $existingDates = $this->team->holidays()
            ->whereYear('date', $this->importYear)
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
            
        $this->availableHolidays = array_filter($this->availableHolidays, function($holiday) use ($existingDates) {
            return !in_array($holiday['date'], $existingDates);
        });
    }

    private function getMunicipalityFromWorkCenters(): ?string
    {
        // Get municipality from the first work center that has one
        $workCenter = $this->team->workCenters()->whereNotNull('city')->first();
        return $workCenter ? $workCenter->city : null;
    }

    public function importSelectedHolidays(): void
    {
        if (empty($this->selectedHolidays)) {
            session()->flash('error', __('Please select at least one holiday to import.'));
            return;
        }

        $imported = 0;
        foreach ($this->selectedHolidays as $index) {
            if (isset($this->availableHolidays[$index])) {
                $holiday = $this->availableHolidays[$index];
                
                $this->team->holidays()->create([
                    'name' => $holiday['name'],
                    'date' => $holiday['date'],
                    'type' => $holiday['type'],
                ]);
                
                $imported++;
            }
        }

        session()->flash('success', __('Successfully imported :count holidays.', ['count' => $imported]));
        
        $this->importingHolidays = false;
        $this->selectedHolidays = [];
        $this->loadHolidays();
    }

    public function updatedImportYear(): void
    {
        if ($this->importingHolidays) {
            $this->loadAvailableHolidays();
        }
    }

    public function toggleSelectAll(): void
    {
        if (count($this->selectedHolidays) === count($this->availableHolidays)) {
            // If all are selected, deselect all
            $this->selectedHolidays = [];
        } else {
            // If not all are selected, select all
            $this->selectedHolidays = array_keys($this->availableHolidays);
        }
    }

    public function getIsAllSelectedProperty(): bool
    {
        return count($this->selectedHolidays) === count($this->availableHolidays) && count($this->availableHolidays) > 0;
    }

    public function importAllHolidays(): void
    {
        if (empty($this->availableHolidays)) {
            session()->flash('error', __('No holidays available to import.'));
            return;
        }

        $imported = 0;
        foreach ($this->availableHolidays as $holiday) {
            $this->team->holidays()->create([
                'name' => $holiday['name'],
                'date' => $holiday['date'],
                'type' => $holiday['type'],
            ]);
            
            $imported++;
        }

        session()->flash('success', __('Successfully imported all :count holidays.', ['count' => $imported]));
        
        $this->importingHolidays = false;
        $this->selectedHolidays = [];
        $this->loadHolidays();
    }

    public function render()
    {
        return view('livewire.teams.holiday-manager', [
            'team' => $this->team,
        ]);
    }
}