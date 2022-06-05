<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class GetTimeRegisters extends Component {

    public $search;
    public $sort = 'startTime';
    public $direction = 'desc';

    protected $listeners = ['render'];

    // TODO Show register in a per user rol basis
    public function render() {     
        $events = Event::where('description', 'like', '%' . $this->search . '%')
                ->where('userId', '=', Auth::user()->id)
                ->orderBy($this->sort, $this->direction)
                ->get();
        return view('livewire.get-time-registers', compact('events'));
    }

    public function order($sort){

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
    }
}