<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class UpdateIrregularEventColorForm extends Component
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
    public array $state = [];

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team): void
    {
        $this->team = $team;
        $this->state['irregular_event_color'] = $this->team->irregular_event_color ?? '#EA8000';
    }

    /**
     * Update the irregular event color for the team.
     *
     * @return void
     */
    public function updateIrregularEventColor(): void
    {
        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->team->forceFill([
            'irregular_event_color' => $this->state['irregular_event_color'],
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
        return view('livewire.teams.update-irregular-event-color-form');
    }
}
