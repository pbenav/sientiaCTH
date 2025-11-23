<div class="space-y-6">
    @livewire('teams.work-center-manager', ['teamId' => $team->id])

    <x-jet-section-border />

    <div class="mt-10 sm:mt-0">
        <div class="max-w-7xl mx-auto">
            @livewire('teams.timezone-manager', ['team' => $team])
        </div>
    </div>
</div>
