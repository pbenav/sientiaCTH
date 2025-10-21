<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Team;

class EventExpirationManager extends Component
{
    public $team;
    public array $state = [];

    public function mount(Team $team)
    {
        $this->team = $team;
        $this->state['event_expiration_days'] = $this->team->event_expiration_days ?? 7;
    }

    public function updateEventExpiration()
    {
        $this->resetErrorBag();

        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->validate([
            'state.event_expiration_days' => ['required', 'integer', 'min:1'],
        ]);

        $this->team->forceFill([
            'event_expiration_days' => $this->state['event_expiration_days'],
        ])->save();

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.teams.event-expiration-manager');
    }
}
