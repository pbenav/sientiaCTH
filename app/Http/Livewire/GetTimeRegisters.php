<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use DateTime;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;

class GetTimeRegisters extends Component
{

    public $search;
    public $event;
    public $open_edit = false;
    public $sort = 'start';
    public $direction = 'desc';

    protected $listeners = ['render'];

    protected $rules = [
        'event.start' => 'required',
        'event.end' => 'required',
        'event.description' => 'required',
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

    public function render()
    {
        $events = Event::where('description', 'like', '%' . $this->search . '%')
            ->where('user_id', '=', Auth::user()->id)
            ->orderBy($this->sort, $this->direction)
            ->get();
        return view('livewire.get-time-registers', compact('events'));
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

    public function edit(Event $ev)
    {
        if($ev->is_open == 1){
            $ev->end = date('Y/m/d H:i:s');                                
        }
        
        $this->event = $ev;
        $this->open_edit = true;
    }

    public function update()
    {        
        //dd($this->event);
        $this->validate();
        $this->event->save();

        $this->reset(["open_edit"]);
        
        $this->emit('alert', 'Event updated!');
    }
}
