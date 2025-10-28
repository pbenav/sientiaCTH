<?php

namespace App\Http\Livewire\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MoveUserForm extends Component
{
    public $team;
    public  $user;
    public $showModal = false;
    public $destinationTeamId;
    public $eligibleTeams;

    public function mount(Team $team, User $user)
    {
        $this->team = $team;
        $this->user = $user;
        $this->eligibleTeams = Auth::user()->allTeams()->where('id', '!=', $this->team->id);
    }

    public function moveUser()
    {
        // Detach from the source team and attach to the destination team
        $this->user->teams()->detach($this->team->id);
        $this->user->teams()->attach($this->destinationTeamId);

        // Move events from the source team to the destination team
        $this->user->events()->where('team_id', $this->team->id)->update(['team_id' => $this->destinationTeamId]);

        $this->emit('userMoved');
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.teams.move-user-form');
    }
}
