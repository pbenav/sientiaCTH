<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

use function PHPUnit\Framework\isNull;

class GetTimeRegisters extends Component
{

    use WithPagination;

    protected $events;
    public $showModalGetTimeRegisters = false;
    public $search;
    public $sort = 'start';
    public $direction = 'desc';
    public $qtytoshow = '10';
    public $readyonload = false;

    protected $listeners = ['render', 'confirm', 'remove'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10']
    ];

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
        # Before modification there is an event for Sweet alert2 to confirm.
        $this->event = $ev;        
        $this->event->confirm();
    }

    public function remove(Event $ev)
    {
        # Before deletion there is an event for Sweet alert2 to confirm.
        $this->event = $ev;
        $this->event->delete();
    }

    public function getEvents()
    {
        $team = Auth::user()->personalTeam()->name;
        dd($team);
        switch (1) {
            case 0:
                echo "i equals 0";
                break;
            case 1:
                echo "i equals 1";
                break;
            case 2:
                echo "i equals 2";
                break;
        }
        if ($this->readyonload) {
            $this->events = Event::where('description', 'like', '%' . $this->search . '%')
                ->where('user_id', '=', Auth::user()->id)
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->qtytoshow);
        } else {
            $this->events = [];
        }
    }

    public function render()
    {
        $this->getEvents();
        return view('livewire.get-time-registers')->with('events', $this->events);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingQtytoshow()
    {
        $this->resetPage();
    }

    public function loadEvents(){
        $this->readyonload = true;
    }
    
}
