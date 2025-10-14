<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UpdateDefaultWorkCenterForm extends Component
{
    public $workCenters;
    public $defaultWorkCenterId;

    public function mount()
    {
        $this->workCenters = auth()->user()->currentTeam->workCenters;
        $defaultWorkCenter = auth()->user()->meta->where('meta_key', 'default_work_center_id')->first();
        $this->defaultWorkCenterId = $defaultWorkCenter ? $defaultWorkCenter->meta_value : '';
    }

    public function updateDefaultWorkCenter()
    {
        auth()->user()->meta()->updateOrCreate(
            ['meta_key' => 'default_work_center_id'],
            ['meta_value' => $this->defaultWorkCenterId]
        );

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.update-default-work-center-form');
    }
}
