<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'settings' }">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- Tab Headers -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'settings', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'settings' }"
                           @click.prevent="tab = 'settings'">
                            {{ __('Ajustes de equipo') }}
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'events', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'events' }"
                           @click.prevent="tab = 'events'">
                            {{ __('Gestión de eventos') }}
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'work_centers', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'work_centers' }"
                           @click.prevent="tab = 'work_centers'">
                            {{ __('Work Centers') }}
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'holidays', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'holidays' }"
                           @click.prevent="tab = 'holidays'">
                            {{ __('Días Festivos') }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div>
                <!-- Team Settings Tab -->
                <div x-show="tab === 'settings'">
                    @livewire('teams.update-team-name-form', ['team' => $team])

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.timezone-manager', ['team' => $team])
                    </div>

                    <x-jet-section-border />

                    @livewire('teams.team-member-manager', ['team' => $team])

                    @if (Gate::check('delete', $team) && ! $team->personal_team)
                        <x-jet-section-border />

                        <div class="mt-10 sm:mt-0">
                            @livewire('teams.delete-team-form', ['team' => $team])
                        </div>
                    @endif
                </div>

                <!-- Event Types Tab -->
                <div x-show="tab === 'events'">
                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.event-type-manager', ['team' => $team])
                    </div>

                    @if (Gate::check('update', $team))
                        <x-jet-section-border />

                        <div class="mt-10 sm:mt-0">
                            @livewire('teams.clock-in-delay-manager', ['team' => $team])
                        </div>
                    @endif
                </div>

                <!-- Work Centers Tab -->
                <div x-show="tab === 'work_centers'">
                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.work-center-manager', ['team' => $team])
                    </div>
                </div>

                <!-- Holidays Tab -->
                <div x-show="tab === 'holidays'">
                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.holiday-manager', ['team' => $team])
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
