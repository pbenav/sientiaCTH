<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use Brick\Math\BigInteger;
use DateTime;

use function PHPUnit\Framework\isNull;

class GetTimeRegisters extends Component
{

    public $search;
    public $event;
    public $events;
    public $showModalGetTimeRegisters = false;
    public $sort = 'start';
    public $direction = 'desc';

    protected $listeners = ['render', 'remove', 'edit'];

    protected $rules = [
        'event.start' => 'required',
        'event.end' => 'required',
        'event.description' => 'required',
    ];

    protected $queryString = [
        'sort', 'direction'
    ];

    public function mount()
    {
        $this->event = new Event();        
    }

    public function startToday()
    {
        $this->event->start = date('Y/m/d H:i:s');
        $this->event->end = date('Y/m/d H:i:s');
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

    public function add(){
        $this->showModalGetTimeRegisters = true;
    }    

    public function edit(Event $ev)
    {
        if ($ev->is_open == 1) {
            $ev->end = date('Y/m/d H:i:s');
            $this->event = $ev;
            $this->showModalGetTimeRegisters = true;
        } else {
            $this->emit('alert', 'Register is confirmed. Can\'t be changed.'); 
            $this->reset(["showModalGetTimeRegisters"]);
        }
    }

    public function update()
    {
        $this->validate();
        $this->event->save();
        $this->reset(["showModalGetTimeRegisters"]);
        $this->emit('alert', 'Event updated!');
    }

    public function confirm(Event $ev)
    {
        # Before modification there is an event for Sweet alert2 to confirm.
        $this->event = $ev ;
        if ($this->event->is_open == 1){
            $this->event->is_open = 0;
            $this->event->save();
        };
        $this->reset(["showModalGetTimeRegisters"]);

    }

    public function remove(Event $ev)
    {
        # Before deletion there is an event for Sweet alert2 to confirm.
        $this->event = $ev;
        $this->event->delete();
        $this->reset(["showModalGetTimeRegisters"]);
    }

    public function getAll()
    {
        return Event::where('description', 'like', '%' . $this->search . '%')
            ->where('user_id', '=', Auth::user()->id)
            ->orderBy($this->sort, $this->direction)
            ->get();
    }

    public function render()
    {
        $this->events = $this->getAll();
        return view('livewire.get-time-registers');
    }
}
