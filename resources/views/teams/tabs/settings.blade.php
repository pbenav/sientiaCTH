@livewire('teams.update-team-name-form', ['team' => $team])

<x-jet-section-border />

<div class="mt-10 sm:mt-0">
    @include('teams.team-information', ['team' => $team])
</div>

@if (Gate::check('delete', $team) && ! $team->personal_team)
    <x-jet-section-border />

    <div class="mt-10 sm:mt-0">
        @livewire('teams.delete-team-form', ['team' => $team])
    </div>
@endif
