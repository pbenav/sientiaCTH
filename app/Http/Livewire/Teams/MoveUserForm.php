<?php

namespace App\Http\Livewire\Teams;

use App\Models\EventType;
use App\Models\Team;
use App\Models\User;
use App\Exceptions\MaxWorkdayDurationExceededException;
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
        $authUser = Auth::user();
        if ($authUser->is_admin) {
            // Global admins can access all teams
            $this->eligibleTeams = Team::where('id', '!=', $this->team->id)->get();
        } else {
            // Filter teams where user can administer
            $this->eligibleTeams = $authUser->allTeams()->filter(function ($t) use ($authUser) {
                return $authUser->ownsTeam($t) || $authUser->hasTeamRole($t, 'admin');
            })->where('id', '!=', $this->team->id);
        }
    }

    public function moveUser()
    {
        $destinationTeam = Team::find($this->destinationTeamId);
        
        $successCount = 0;
        $failCount = 0;

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
                    try {
                        // Desactivar temporalmente el observer para evitar la validación de MaxWorkdayDuration
                        // ya que solo estamos moviendo de equipo, no cambiando la duración real.
                        $event->updateQuietly([
                            'team_id' => $destinationTeam->id,
                            'event_type_id' => $newEventType->id,
                        ]);
                        $successCount++;
                    } catch (\Exception $e) {
                        \Log::error('Error moving event in MoveUserForm', ['event_id' => $event->id, 'error' => $e->getMessage()]);
                        $failCount++;
                    }
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

        // Refresh the teams relationship so the user can immediately switch to this team
        $this->user->load('teams');

        $this->emit('userMoved');
        $this->showModal = false;
        
        if ($this->transferEvents) {
            $message = __('User moved successfully.') . ' ' . __('Events transferred: :success.', ['success' => $successCount]);
            if ($failCount > 0) {
                $message .= ' ' . __(':fail events could not be transferred.', ['fail' => $failCount]);
                $this->dispatchBrowserEvent('swal:modal-reload', [
                    'type' => 'warning',
                    'title' => __('Movement Summary'),
                    'text' => $message,
                ]);
            } else {
                $this->dispatchBrowserEvent('swal:modal-reload', [
                    'type' => 'success',
                    'title' => __('Movement Summary'),
                    'text' => $message,
                ]);
            }
        } else {
             $this->emit('alert', __('User moved successfully.'));
        }
    }

    public function render()
    {
        return view('livewire.teams.move-user-form');
    }
}
