<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use Livewire\Component;

/**
 * A Livewire component for managing clock-in delay and event expiration settings
 * for a team.
 *
 * This component provides a form for team administrators to configure settings
 * related to clock-in delays and automatic event expiration.
 */
class ClockInDelayManager extends Component
{
    /**
     * The team instance.
     *
     * @var \App\Models\Team
     */
    public $team;

    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Mount the component.
     *
     * @param  \App\Models\Team  $team
     * @return void
     */
    public function mount($team): void
    {
        $this->team = $team;
        $this->state = $team->withoutRelations()->toArray();
    }

    /**
     * Update the clock-in delay settings.
     *
     * @return void
     */
    public function updateClockInDelaySettings(): void
    {
        $this->resetErrorBag();

        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->team->forceFill([
            'force_clock_in_delay' => $this->state['force_clock_in_delay'] ?? false,
            'clock_in_delay_minutes' => $this->state['clock_in_delay_minutes'] ?? null,
            'clock_in_grace_period_minutes' => $this->state['clock_in_grace_period_minutes'] ?? null,
        ])->save();

        $this->emit('saved');
    }

    /**
     * Update the event expiration settings.
     *
     * @return void
     */
    public function updateEventExpirationSettings(): void
    {
        $this->resetErrorBag();

        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->team->forceFill([
            'event_expiration_days' => $this->state['event_expiration_days'] ?? null,
        ])->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.clock-in-delay-manager');
    }
}
