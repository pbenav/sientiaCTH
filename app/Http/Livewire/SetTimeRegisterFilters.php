<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SetTimeRegisterFilters extends Component
{
    public $showFiltersModal = false;
    public $filt;

    protected $listeners = ['open', 'setFilters'];

    protected $rules = [
        'filter.start' => 'nullable|date',
        'filter.end' => 'nullable|date|after:filter.start',
        'filter.name' => 'nullable|string',
        'filter.family_name1' => 'nullable|string',
        'filter.is_open' => 'boolean',
        'filter.description' => 'nullable|string',
    ];
    
    public function mount()
    {
        $this->filt = [
            "start" => date('Y-01-01'),
            "end" => date('Y-m-t'),
            "name" => "",
            "family_name1" => "",
            "is_open" => false,
            "description" => __('All'),
        ];  
    }
    
    public function open(){
        $this->showFiltersModal = true;
    }
    
    public function setFilters()
    {           
        $this->reset(['showFiltersModal']);
        $this->emitTo('get-time-registers', 'setFilter', $this->filt);
    } 
        public function unSetFilters()
    {           
        $this->reset(['showFiltersModal']);
        $this->emitTo('get-time-registers', 'unsetfilter');
    } 

    public function render()
    {  
        return view('livewire.set-time-register-filters');
    }
}