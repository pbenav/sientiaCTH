<?php

namespace App\Http\Livewire\Teams;

use App\Models\WorkCenter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\Component;

/**
 * A Livewire component for managing work centers for a team.
 *
 * This component provides functionality for creating, updating, and deleting
 * work centers.
 */
class WorkCenterManager extends Component
{
    use AuthorizesRequests;

    public $team;
    public bool $managingWorkCenters = true;

    public bool $confirmingWorkCenterRemoval = false;
    public ?int $workCenterIdBeingRemoved = null;

    public bool $confirmingWorkCenterCreation = false;
    public bool $confirmingWorkCenterUpdate = false;

    public array $state = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:work_centers,code,'.($this->state['id'] ?? null),
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
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
    }

    /**
     * Show the form for creating a new work center.
     *
     * @return void
     */
    public function confirmWorkCenterCreation(): void
    {
        $this->resetErrorBag();
        $this->state = [];
        $this->confirmingWorkCenterCreation = true;
    }

    /**
     * Create a new work center.
     *
     * @return void
     */
    public function createWorkCenter(): void
    {
        $this->resetErrorBag();

        Validator::make($this->state, $this->rules())->validate();

        $this->team->workCenters()->create($this->state);

        $this->confirmingWorkCenterCreation = false;
        $this->emit('saved');
    }

    /**
     * Show the form for updating a work center.
     *
     * @param \App\Models\WorkCenter $workCenter
     * @return void
     */
    public function confirmWorkCenterUpdate(WorkCenter $workCenter): void
    {
        $this->resetErrorBag();
        $this->state = $workCenter->toArray();
        $this->confirmingWorkCenterUpdate = true;
    }

    /**
     * Update a work center.
     *
     * @return void
     */
    public function updateWorkCenter(): void
    {
        $this->resetErrorBag();

        Validator::make($this->state, $this->rules())->validate();

        WorkCenter::find($this->state['id'])->update($this->state);

        $this->confirmingWorkCenterUpdate = false;
        $this->emit('saved');
    }

    /**
     * Confirm the removal of a work center.
     *
     * @param int $workCenterId
     * @return void
     */
    public function confirmWorkCenterRemoval(int $workCenterId): void
    {
        $this->confirmingWorkCenterRemoval = true;
        $this->workCenterIdBeingRemoved = $workCenterId;
    }

    /**
     * Remove a work center.
     *
     * @return void
     */
    public function removeWorkCenter(): void
    {
        WorkCenter::find($this->workCenterIdBeingRemoved)->delete();
        $this->confirmingWorkCenterRemoval = false;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.work-center-manager', [
            'workCenters' => $this->team->workCenters()->paginate(5)
        ]);
    }
}
