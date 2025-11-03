<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'settings' }" class="w-full">
                <div class="flex border-b border-gray-200">
                    <button @click="tab = 'settings'" :class="{ 'border-b-2 border-indigo-500': tab === 'settings' }" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                        {{ __('Team Preferences') }}
                    </button>
                    <button @click="tab = 'event_management'" :class="{ 'border-b-2 border-indigo-500': tab === 'event_management' }" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                        {{ __('Event Management') }}
                    </button>
                    <button @click="tab = 'work_centers'" :class="{ 'border-b-2 border-indigo-500': tab === 'work_centers' }" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                        {{ __('Work Centers') }}
                    </button>
                    <button @click="tab = 'user_management'" :class="{ 'border-b-2 border-indigo-500': tab === 'user_management' }" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                        {{ __('User Management') }}
                    </button>
                    <button @click="tab = 'announcements'" :class="{ 'border-b-2 border-indigo-500': tab === 'announcements' }" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none">
                        {{ __('Announcements') }}
                    </button>
                </div>

                {{-- Team Preferences Tab --}}
                <div x-show="tab === 'settings'" class="py-6">
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
                </div>

                {{-- Event Management Tab --}}
                <div x-show="tab === 'event_management'" class="py-6">
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
                        @livewire('teams.update-irregular-event-color-form', ['team' => $team])
                    </div>
                </div>

                {{-- Work Centers Tab --}}
                <div x-show="tab === 'work_centers'" class="py-6">
                    @livewire('teams.work-center-manager', ['team' => $team])
                    <x-jet-section-border />
                    <div class="mt-10 sm:mt-0">
                        @livewire('teams.timezone-manager', ['team' => $team])
                    </div>
                </div>

                {{-- User Management Tab --}}
                <div x-show="tab === 'user_management'" class="py-6" style="display: none;">
                    @livewire('teams.team-member-manager', ['team' => $team])
                </div>

                {{-- Announcements Tab --}}
                <div x-show="tab === 'announcements'" class="py-6" style="display: none;">
                    @livewire('teams.announcement-manager', ['team' => $team])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
