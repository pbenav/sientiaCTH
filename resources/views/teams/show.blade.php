<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div>
                @livewire('teams.update-team-name-form', ['team' => $team])

                @if (Gate::check('delete', $team) && ! $team->personal_team)
                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.delete-team-form', ['team' => $team])
                    </div>
                @endif
            </div>

            <div class="mt-10 sm:mt-0">
                @livewire('teams.event-type-manager', ['team' => $team])
                <x-jet-section-border />
                @livewire('teams.clock-in-delay-manager', ['team' => $team])
                <x-jet-section-border />
                @livewire('teams.holiday-manager', ['team' => $team])
                <x-jet-section-border />
                @livewire('teams.update-irregular-event-color-form', ['team' => $team])
            </div>

            <div class="mt-10 sm:mt-0">
                @livewire('teams.work-center-manager', ['team' => $team])
                <x-jet-section-border />
                @livewire('teams.timezone-manager', ['team' => $team])
            </div>

            <div class="mt-10 sm:mt-0">
                @livewire('teams.team-member-manager', ['team' => $team])
            </div>
        </div>
    </div>
</x-app-layout>
