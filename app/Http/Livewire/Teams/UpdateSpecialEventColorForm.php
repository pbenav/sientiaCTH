<?php

namespace App\Http\Livewire\Teams;

use Livewire\Component;
use Illuminate\Support\Facades\Gate;

class UpdateSpecialEventColorForm extends Component
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
    public function mount($team): void
    {
        $this->team = $team;

        $this->state['special_event_color'] = $this->team->special_event_color ?? '#EA8000';
    }

    /**
     * Update the special event color for the team.
     *
     * @return void
     */
    public function updateSpecialEventColor(): void
    {
        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->team->update([
            'special_event_color' => $this->state['special_event_color'],
        ]);

        $this->emit('saved');
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return auth()->user();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.update-special-event-color-form');
    }
}