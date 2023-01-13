<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Nette\Utils\Paginator;

class GetTimeRegisters extends Component
{

    use WithPagination;
    use HasTeams;

    
    protected $events;
    public $showModalGetTimeRegisters = false;
    public $showFiltersModal = false;
    public $search;
    public $filter;
    public $sort = 'start';
    public $direction = 'desc';
    public $qtytoshow = '10';
    public $readyonload = false;
    public $user;
    public $team;
    public $isTeamAdmin;
    public $isInspector;
    public $confirmed;
    public $filtered;

    protected $listeners = ['render', 'confirm', 'remove'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10']
    ];

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
        $this->filter = new Event([
            "start" => date('Y-m-01'),
            "end" => null, //date('Y-m-t'),
            "name" => "",
            "family_name1" => "",
            "is_open" => false,
            "description" => __('All'),
        ]);        
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
        $this->isTeamAdmin = $this->user->isTeamAdmin();
        $this->isInspector = $this->user->isInspector();
        $this->confirmed = false;
        $this->filtered = false;
       
    }

    public function order($sort)
    {
        if ($this->sort = $sort) {
            if ($this->direction == 'asc') {
                $this->direction = 'desc';
            } else {
                $this->direction = 'asc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
        ;
    }

    public function confirm(Event $ev)
    {
        #Before modification there is an event for Sweet alert2 to confirm.
        $this->event = $ev;
        $this->event->confirm();
    }

    public function remove(Event $ev)
    {
        #Before deletion there is an event for Sweet alert2 to confirm.
        $this->event = $ev;
        if ($this->isTeamAdmin) {
            $this->event->delete();
        } else if ($this->event->is_open) {
            $this->event->delete();
        }
        $this->emitSelf('render');
    }

    public function unsetFilter()
    {
        $this->showFiltersModal = false;
        $this->filtered = false;
        $this->confirmed = false;
    }

    public function setFilter()
    {
        $this->showFiltersModal = true;
        $this->filtered = true;
        $this->confirmed = false;
    }
    
    public function getEvents()
    {
        // Check if user is admin
        $teamUsers = array();
        if ($this->isTeamAdmin || $this->isInspector) {
            foreach ($this->team->allUsers() as $us) {
                array_push($teamUsers, $us->id);
            }
        } else {
            array_push($teamUsers, $this->user->id);
        }

        if ($this->readyonload) {
            // Get events taking account of is_team_admin and search strings
            if ($this->filtered) {
                $this->events = $this->filter->getEventsFiltered($teamUsers, $this->filtered, $this->filter, $this->sort, $this->direction, $this->qtytoshow);
            } else {              
                $this->events = $this->filter->getEventsPerUser($teamUsers, $this->confirmed, $this->search, $this->sort, $this->direction, $this->qtytoshow);
            }
        } else {
            $this->events = [];
        }
    }

    public function render()
    {
        $this->getEvents();
        return view('livewire.get-time-registers', )
            ->with('events', $this->events)
            ->with('isTeamAdmin', $this->isTeamAdmin)
            ->with('isInspector', $this->isInspector);
    }

    public function updatingEvent()
    {
        $this->resetPage();
    }

    public function updatingQtytoshow()
    {
        $this->resetPage();
    }

    public function loadEvents()
    {
        $this->readyonload = true;
    }
}