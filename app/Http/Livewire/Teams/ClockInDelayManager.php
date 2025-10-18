<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use Livewire\Component;

class ClockInDelayManager extends Component
{
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
        $this->state = $team->withoutRelations()->toArray();
    }

    /**
     * Update the clock-in delay settings.
     *
     * @return void
     */
    public function updateClockInDelaySettings()
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
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.clock-in-delay-manager');
    }
}
