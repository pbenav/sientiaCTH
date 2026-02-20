<?php

namespace App\Http\Livewire;

use Livewire\Component;

/**
 * A Livewire component for updating the default work center.
 *
 * This component provides a form for users to select their default work center.
 */
class UpdateDefaultWorkCenterForm extends Component
{
    /**
     * The collection of work centers.
     *
     * @var \Illuminate\Support\Collection
     */
    public $workCenters;

    /**
     * The ID of the default work center.
     *
     * @var int|null
     */
    public ?int $defaultWorkCenterId = null;

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->workCenters = auth()->user()->currentTeam->workCenters;
        $teamId = auth()->user()->currentTeam->id;
        $defaultWorkCenter = auth()->user()->meta->where('meta_key', 'default_work_center_id_team_' . $teamId)->first();
        $this->defaultWorkCenterId = $defaultWorkCenter ? (int) $defaultWorkCenter->meta_value : null;
    }

    /**
     * Update the default work center.
     *
     * @return void
     */
    public function updateDefaultWorkCenter(): void
    {
        $teamId = auth()->user()->currentTeam->id;
        auth()->user()->meta()->updateOrCreate(
            ['meta_key' => 'default_work_center_id_team_' . $teamId],
            ['meta_value' => $this->defaultWorkCenterId]
        );

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.update-default-work-center-form');
    }
}
