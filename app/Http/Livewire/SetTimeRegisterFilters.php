<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use App\Models\Event;
use Livewire\Component;

class SetTimeRegisterFilters extends Component
{
    public $showFiltersModal = false;
    public Event $filter;


    protected $listeners = ['open', 'setFilters'];

    protected $rules = [
        'filter.start' => 'date',
        'filter.end' => 'date',
        'filter.name' => 'string',
        'filter.family_name1' => 'string',
        'filter.is_open' => 'boolean',
        'filter.description' => 'string',
    ];
    
    public function mount()
    {
        $this->filter = new Event();
        $this->filter->start = Carbon::today();
        $this->filter->end = Carbon::today();
        $this->filter->description = __('All');
    }

    public function open(){
        $this->showFiltersModal = true;
    }

    public function setFilters()
    {           
        $this->reset(['showFiltersModal']);
        $this->emitTo('get-time-registers', 'filter', 'filter');
    }

    public function render()
    {
        return view('livewire.set-time-register-filters')->with('filter', $this->filter);
    }
}
