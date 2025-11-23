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
    public $transferEvents = false;

    public function mount(Team $team, User $user)
    {
        $this->team = $team;
        $this->user = $user;
        
        // Get teams where the authenticated user is owner or admin
        $this->eligibleTeams = Auth::user()->allTeams()->filter(function ($t) {
            return Auth::user()->ownsTeam($t) || Auth::user()->hasTeamRole($t, 'admin');
        })->where('id', '!=', $this->team->id);
    }

    public function moveUser()
    {
        $destinationTeam = Team::find($this->destinationTeamId);

        if ($this->transferEvents) {
            // Get all unique event types for the user across ALL teams (not just current one)
            // as requested: "sean del equipo anterior que sean"
            $userEvents = $this->user->events()->with('eventType')->get();
            
            // Group events by event type to handle mapping efficiently
            $eventsByType = $userEvents->groupBy('event_type_id');

            foreach ($eventsByType as $typeId => $events) {
                $sourceEventType = $events->first()->eventType;
                
                if (!$sourceEventType) continue;

                // Check if an event type with the same name exists in the destination team
                // or create it
                $newEventType = $destinationTeam->eventTypes()->firstOrCreate(
                    ['name' => $sourceEventType->name],
                    [
                        'color' => $sourceEventType->color,
                        'icon' => $sourceEventType->icon,
                        'is_vacation' => $sourceEventType->is_vacation,
                        // Copy other relevant fields from source event type if needed
                    ]
                );

                // Update all these events to the new team and new event type
                foreach ($events as $event) {
                    $event->update([
                        'team_id' => $destinationTeam->id,
                        'event_type_id' => $newEventType->id,
                    ]);
                }
            }
        }

        // Get the user's role in the source team, or default to 'editor'
        $role = optional($this->user->teamRole($this->team))->key ?? 'editor';

        // Detach from the source team
        $this->user->teams()->detach($this->team->id);
        
        // Check if user is already in destination team to avoid duplicates
        if (!$this->user->belongsToTeam($destinationTeam)) {
            $this->user->teams()->attach($this->destinationTeamId, ['role' => $role]);
        }

        $this->emit('userMoved');
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.teams.move-user-form');
    }
}
