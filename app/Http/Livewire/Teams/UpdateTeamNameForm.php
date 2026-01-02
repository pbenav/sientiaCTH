<?php

namespace App\Http\Livewire\Teams;

use Laravel\Jetstream\Http\Livewire\UpdateTeamNameForm as JetstreamUpdateTeamNameForm;

class UpdateTeamNameForm extends JetstreamUpdateTeamNameForm
{
    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        parent::mount($team);

        $this->state['max_member_teams'] = $team->max_member_teams;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.update-team-name-form');
    }
}
