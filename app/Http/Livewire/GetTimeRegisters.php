<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;

class GetTimeRegisters extends Component
{

    use WithPagination;
    use HasTeams;

    public $event;
    public $showModalGetTimeRegisters = false;
    public $search;
    public $filter;
    public $sort = 'start';
    public $direction = 'desc';
    public $qtytoshow = '10';
    public $readyonload = false;
    public $user;
    public $team;
    public $is_team_admin;
    public $is_inspector;
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
        $this->is_team_admin = $this->user->isTeamAdmin();
        $this->is_inspector = $this->user->isInspector();
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
        if ($this->is_team_admin) {
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

    public function setFilter($filter)
    {
        $f = json_decode($filter);
        $this->filter->start = $f->start;
        $this->filter->end = $f->end;
        $this->filter->name = $f->name;
        $this->filter->family_name1 = $f->family_name1;
        $this->filter->is_open = $f->is_open;
        $this->filter->description = $f->description;
        $this->filtered = true;
    }

    public function getEventsPerUser($wc)
    {
        return Event::select(
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
                if ($this->confirmed) {
                    $query->where('is_open', '1');
                }            
                if ($this->filtered) {
                    if (!is_null($this->filter->start)) {
                        $query->whereDate('start', '>=', $this->filter->start);
                    }

                    if (!is_null($this->filter->start)) {
                        $query->whereDate('end', '<=', $this->filter->end);
                    }

                    if (!empty($this->filter->name)) {
                        $query->where('name', 'like', $this->filter->name);
                    }

                    if (!empty($this->filter->family_name1)) {
                        $query->where('family_name1', $this->filter->family_name1);
                    }

                    if ($this->filter->is_open) {
                        $query->where('is_open', '1');
                    }

                    if ($this->filter->description != __('All')) {
                        $query->where('description', $this->filter->description);
                    }
                } else {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('user_id', $this->search)
                        ->orWhere('family_name1', 'like', '%' . $this->search . '%')
                        ->orWhere('family_name2', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                }
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->qtytoshow);
    }

    public function getEvents()
    {
        // Check if user is admin
        $teamUsers = array();
        if ($this->is_team_admin || $this->is_inspector) {
            foreach ($this->team->allUsers() as $us) {
                array_push($teamUsers, $us->id);
            }
        } else {
            array_push($teamUsers, $this->user->id);
        }

        // Get events taking account of is_team_admin and search strings
        if ($this->readyonload) {
        }
        return $this->getEventsPerUser($teamUsers);
    }

    public function render()
    {          
        //return view('livewire.get-time-registers', compact('events', 'isTeamAdmin', 'isInspector'));
        // return view('livewire.get-time-registers',)
        //     ->with('events', $this->getEvents())
        //     ->with('isTeamAdmin', $this->is_team_admin)
        //     ->with('isInspector', $this->is_inspector);

            return view('livewire.get-time-registers',[
                'events' => $this->getEvents(),
                'isTeamAdmin' => $this->is_team_admin,
                'isInspector' => $this->is_inspector,
             ]);

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