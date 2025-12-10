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
            
            \Log::info('TeamAnnouncements::mount', [
                'user_id' => Auth::id(),
                'team_id' => $this->team->id,
                'team_name' => $this->team->name,
                'announcements_count' => $this->announcements->count(),
                'announcement_ids' => $this->announcements->pluck('id')->toArray(),
                'announcement_team_ids' => $this->announcements->pluck('team_id')->toArray(),
            ]);
        } else {
            $this->announcements = collect();
            \Log::info('TeamAnnouncements::mount - No team');
        }
    }

    public function render()
    {
        return view('livewire.team-announcements');
    }
}
