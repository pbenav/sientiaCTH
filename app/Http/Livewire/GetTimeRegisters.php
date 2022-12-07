<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;

class GetTimeRegisters extends Component
{

    use WithPagination;
    use HasTeams;

    protected $events;

    public $event;
    public $showModalGetTimeRegisters = false;
    public $search;
    public Event $filter;
    public $sort = 'start';
    public $direction = 'desc';
    public $qtytoshow = '10';
    public $readyonload = false;
    public $user;
    public $team;
    public $is_team_admin;
    public $is_inspector;
    public $confirmed;

    protected $listeners = ['filter','render', 'confirm', 'remove'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10']
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
        $this->is_team_admin = $this->user->isTeamAdmin();
        $this->is_inspector = $this->user->isInspector();
        $this->filter = new Event();
        $this->confirmed = false;
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
        };
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
        if ($this->is_team_admin) {
            $this->event->delete();
        } else if ($this->event->is_open) {
            $this->event->delete();
        }
        $this->emitSelf('render');
    }

    public function filter(Event $filter){
        $this->filter = $filter;
    }

    public function getEventsPerUser($wc){
        $datos = Event::select(
            'events.id',
            'events.user_id',
            'users.name',
            'users.family_name1',
            'events.start',
            'events.end',
            'events.description',
            'events.is_open'
        )
            ->join('users', 'events.user_id', '=', 'users.id')
            ->whereIn('events.user_id', $wc)
            ->where(function ($query) {
                if($this->confirmed){
                $query->where('is_open', '=', '1');
                }
            })
            ->where(function ($query) {
                if (!is_null($this->filter->start)) {
                    $query->whereDate('start', '>=', Carbon::parse($this->filter->start))
                    ->whereDate('end', '<=', Carbon::parse($this->filter->end))
                    ->where('name', '=', $this->filter->name)
                    ->where('family_name1', '=', $this->filter->family_name1)
                    ->where('description',  '=', $this->filter->description);
                } else {
                    $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('user_id', $this->search)
                    ->orWhere('family_name1', 'like', '%' . $this->search . '%')
                    ->orWhere('family_name2', 'like', '%' . $this->search . '%')
                    ->orWhere('description',  'like', '%' . $this->search . '%');
                }
                    })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->qtytoshow);

            return $datos;
    }

    public function getEvents()
    {
        // Check if user is admin
        $where_clause = array();
        if ($this->is_team_admin || $this->is_inspector) {
            foreach ($this->team->allUsers() as $us) {
                array_push($where_clause, $us->id);
            }
        } else {
            array_push($where_clause, $this->user->id);
        }

        // Get events taking account of is_team_admin and search strings
        if ($this->readyonload) {
            $this->events = $this->getEventsPerUser($where_clause);

        } else {
            $this->events = [];
        }
    }

    public function render()
    {
        $this->getEvents();
        return view('livewire.get-time-registers')
                ->with('events', $this->events)
                ->with('isTeamAdmin', $this->is_team_admin)
                ->with('isInspector', $this->is_inspector);
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