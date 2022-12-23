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

    public $event;
    protected $events;
    public $showModalGetTimeRegisters = false;
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

    protected $listeners = ['setFilter', 'unsetFilter', 'render', 'confirm', 'remove'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10']
    ];

    public function mount()
    {
        $this->filter = new Event();
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
        $this->filtered = false;
    }

    public function setFilter($f)
    {        
        $this->filter->start = $f["start"];
        $this->filter->end = $f["end"];
        $this->filter->name = $f["name"];
        $this->filter->family_name1 = $f["family_name1"];
        $this->filter->is_open = $f["is_open"];
        $this->filter->description = $f["description"];
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
                //public function getEventsFiltered($teamusers, $filtered, Event $filter, $sort, $direction, $qtytoshow)
                $this->events = $this->filter->getEventsFiltered($teamUsers, $this->filtered, $this->filter, $this->sort, $this->direction, $this->qtytoshow);
            } else {
                //public function getEventsPerUser($teamusers, $search, $confirmed, $sort, $direction, $qtytoshow)                
                $this->events = $this->filter->getEventsPerUser($teamUsers, $this->search, $this->confirmed, $this->sort, $this->direction, $this->qtytoshow);
                //dump($this->events);
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

    public function updatingSearch()
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