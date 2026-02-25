<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TeamAnnouncements extends Component
{
    public $announcements;
    public $team;

    public function mount()
    {
        $this->team = Auth::user()->currentTeam;
        
        if ($this->team) {
            $this->announcements = $this->team->announcements()
                ->active()
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->announcements = collect();
        }
    }

    public function render()
    {
        return view('livewire.team-announcements');
    }
}
