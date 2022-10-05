<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\User;
use Laravel\Jetstream\HasTeams;

class GetTimeRegisters extends Component
{

    use WithPagination;
    use HasTeams;

    protected $events;
    public $showModalGetTimeRegisters = false;
    public $search;
    public $sort = 'start';
    public $direction = 'desc';
    public $qtytoshow = '10';
    public $readyonload = false;
    public $user;
    public $team;
    public $is_admin;

    protected $listeners = ['render', 'confirm', 'remove'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10']
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
        $this->is_admin = $this->user->hasTeamRole($this->team, 'admin');
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
        if ($this->is_admin) {
            $this->event->delete();
        } else if ($this->event->is_open){
            $this->event->delete();
        }
    }

    public function getEvents()
    {
        $where_clause = array();
        if ($this->is_admin) {
            foreach ($this->team->allUsers() as $us) {
                array_push($where_clause, $us->id);
            }
        } else {
            array_push($where_clause, $this->user->id);
        }
        if ($this->readyonload) {
            $this->events = Event::where('description', 'like', '%' . $this->search . '%')
                ->WhereIn('user_id', $where_clause)
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->qtytoshow);
        } else {
            $this->events = [];
        }
    }

    public function render()
    {
        $this->getEvents();
        return view('livewire.get-time-registers')->with('events', $this->events)->with('isAdmin', $this->is_admin);
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
