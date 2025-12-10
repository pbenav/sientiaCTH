@if(auth()->user()->belongsToTeam($team) || auth()->user()->isAdmin())
    @livewire('teams.event-type-manager', ['team' => $team])
@endif

<x-jet-section-border />

@if(auth()->user()->belongsToTeam($team) || auth()->user()->isAdmin())
    <div class="mt-10 sm:mt-0">
        @livewire('teams.clock-in-delay-manager', ['team' => $team])
    </div>
@endif

<x-jet-section-border />

@if(auth()->user()->belongsToTeam($team) || auth()->user()->isAdmin())
    <div class="mt-10 sm:mt-0">
        @livewire('teams.holiday-manager', ['team' => $team])
    </div>
@endif

<x-jet-section-border />

@if(auth()->user()->belongsToTeam($team) || auth()->user()->isAdmin())
    <div class="mt-10 sm:mt-0">
        @livewire('teams.update-special-event-color-form', ['team' => $team])
    </div>
@endif
