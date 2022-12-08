<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SetTimeRegisterFilters extends Component
{
    public $showFiltersModal = false;
    public Event $filter;

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
        $this->filter = new Event();
        $this->filter->start = date('Y-m-01');
        $this->filter->end = date('Y-m-t');
        $this->filter->name = "";
        $this->filter->family_name1 = "";
        $this->filter->is_open = false;
        $this->filter->description = __('All');        
    }

    public function open(){
        $this->showFiltersModal = true;
    }

    public function setFilters()
    {           
        $this->reset(['showFiltersModal']);
        $this->emitTo('get-time-registers', 'filter', $this->filter->toJson());
    } 
        public function unSetFilters()
    {           
        $this->reset(['showFiltersModal']);
        $this->emitTo('get-time-registers', 'filter', null);
    } 

    public function render()
    {
        return view('livewire.set-time-register-filters')->with('filter', $this->filter);
    }
}