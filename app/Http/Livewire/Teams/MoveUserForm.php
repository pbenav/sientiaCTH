<?php

namespace App\Http\Livewire\Teams;

use App\Models\EventType;
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
        $this->eligibleTeams = Auth::user()->ownedTeams->where('id', '!=', $this->team->id);
    }

    public function moveUser()
    {
        $destinationTeam = Team::find($this->destinationTeamId);

        // Get all unique event types for the user in the source team
        $eventTypes = $this->user->events()
            ->where('team_id', $this->team->id)
            ->with('eventType')
            ->get()
            ->pluck('eventType')
            ->unique();

        foreach ($eventTypes as $eventType) {
            if (!$eventType) continue;

            // Check if an event type with the same name already exists in the destination team
            $newEventType = $destinationTeam->eventTypes()->firstOrCreate(
                ['name' => $eventType->name],
                $eventType->toArray()
            );

            // Update the user's events with the new team_id and event_type_id
            $this->user->events()
                ->where('team_id', $this->team->id)
                ->where('event_type_id', $eventType->id)
                ->update([
                    'team_id' => $destinationTeam->id,
                    'event_type_id' => $newEventType->id,
                ]);
        }

        // Get the user's role in the source team, or default to 'editor'
        $role = optional($this->user->teamRole($this->team))->key ?? 'editor';

        // Detach from the source team and attach to the destination team with the same role
        $this->user->teams()->detach($this->team->id);
        $this->user->teams()->attach($this->destinationTeamId, ['role' => $role]);

        $this->emit('userMoved');
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.teams.move-user-form');
    }
}
