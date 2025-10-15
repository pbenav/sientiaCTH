<?php

namespace App\Http\Livewire\Teams;

use App\Models\WorkCenter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\Component;

class WorkCenterManager extends Component
{
    use AuthorizesRequests;

    public $team;
    public $managingWorkCenters = true;

    public $confirmingWorkCenterRemoval = false;
    public $workCenterIdBeingRemoved = null;

    public $confirmingWorkCenterCreation = false;
    public $confirmingWorkCenterUpdate = false;

    public $state = [];

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

    public function mount($team)
    {
        $this->team = $team;
    }

    public function confirmWorkCenterCreation()
    {
        $this->resetErrorBag();
        $this->state = [];
        $this->confirmingWorkCenterCreation = true;
    }

    public function createWorkCenter()
    {
        $this->resetErrorBag();

        Validator::make($this->state, $this->rules())->validate();

        $this->team->workCenters()->create($this->state);

        $this->confirmingWorkCenterCreation = false;
        $this->emit('saved');
    }

    public function confirmWorkCenterUpdate(WorkCenter $workCenter)
    {
        $this->resetErrorBag();
        $this->state = $workCenter->toArray();
        $this->confirmingWorkCenterUpdate = true;
    }

    public function updateWorkCenter()
    {
        $this->resetErrorBag();

        Validator::make($this->state, $this->rules())->validate();

        WorkCenter::find($this->state['id'])->update($this->state);

        $this->confirmingWorkCenterUpdate = false;
        $this->emit('saved');
    }

    public function confirmWorkCenterRemoval($workCenterId)
    {
        $this->confirmingWorkCenterRemoval = true;
        $this->workCenterIdBeingRemoved = $workCenterId;
    }

    public function removeWorkCenter()
    {
        WorkCenter::find($this->workCenterIdBeingRemoved)->delete();
        $this->confirmingWorkCenterRemoval = false;
    }

    public function render()
    {
        return view('livewire.teams.work-center-manager', [
            'workCenters' => $this->team->workCenters()->paginate(5)
        ]);
    }
}
