@livewire('teams.event-type-manager', ['team' => $team])

<x-jet-section-border />

<div class="mt-10 sm:mt-0">
    @livewire('teams.clock-in-delay-manager', ['team' => $team])
</div>

<x-jet-section-border />

<div class="mt-10 sm:mt-0">
    @livewire('teams.holiday-manager', ['team' => $team])
</div>

<x-jet-section-border />

<div class="mt-10 sm:mt-0">
    @livewire('teams.update-special-event-color-form', ['team' => $team])
</div>
