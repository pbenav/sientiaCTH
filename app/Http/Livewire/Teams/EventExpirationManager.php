<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EventExpirationManager extends Component
{
    public $team;
    public $state = [];

    public function mount($team)
    {
        $this->team = $team;
        $this->state['event_expiration_days'] = $this->team->event_expiration_days;
    }

    public function updateEventExpiration()
    {
        $this->resetErrorBag();

        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->validate([
            'state.event_expiration_days' => ['nullable', 'integer', 'min:0'],
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
