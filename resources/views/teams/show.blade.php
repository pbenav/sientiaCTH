<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-[90rem] mx-auto py-10 sm:px-6 lg:px-8">
            {{-- Tabs Navigation --}}
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px" role="tablist">
                    <li class="mr-2" role="presentation">
                        <a href="?tab=settings" 
                           class="inline-block p-4 border-b-2 rounded-t-lg {{ request('tab', 'settings') === 'settings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                           role="tab">
                            {{ __('Team Preferences') }}
                        </a>
                    </li>
                    
                    @if (Auth::user()->belongsToTeam($team) || Auth::user()->is_admin)
                        <li class="mr-2" role="presentation">
                            <a href="?tab=event_management" 
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ request('tab') === 'event_management' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                               role="tab">
                                {{ __('Event Management') }}
                            </a>
                        </li>
                    @endif
                    
                    @if (Auth::user()->is_admin || Auth::user()->ownsTeam($team) || Auth::user()->hasTeamRole($team, 'admin'))
                        <li class="mr-2" role="presentation">
                            <a href="?tab=work_centers" 
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ request('tab') === 'work_centers' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                               role="tab">
                                {{ __('Work Centers') }}
                            </a>
                        </li>
                        <li class="mr-2" role="presentation">
                            <a href="?tab=user_management" 
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ request('tab') === 'user_management' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                               role="tab">
                                {{ __('User Management') }}
                            </a>
                        </li>

                        @if (Auth::user()->is_admin)
                            <li class="mr-2" role="presentation">
                                <a href="?tab=technical_service" 
                                   class="inline-block p-4 border-b-2 rounded-t-lg {{ request('tab') === 'technical_service' ? 'border-indigo-500 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                   role="tab">
                                    {{ __('System Settings') }}
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>
            </div>

            {{-- Tab Content --}}
            <div class="py-6">
                @switch(request('tab', 'settings'))
                    @case('settings')
                        @include('teams.tabs.settings', ['team' => $team])
                        @break

                    @case('event_management')
                        @if (Auth::user()->belongsToTeam($team) || Auth::user()->is_admin)
                            @include('teams.tabs.event-management', ['team' => $team])
                        @else
                            <div class="p-4 bg-red-100 text-red-700 rounded">
                                {{ __('Unauthorized action') }}
                            </div>
                        @endif
                        @break

                    @case('work_centers')
                        @if (Auth::user()->is_admin || Auth::user()->ownsTeam($team) || Auth::user()->hasTeamRole($team, 'admin'))
                            @include('teams.tabs.work-centers', ['team' => $team])
                        @else
                            <div class="p-4 bg-red-100 text-red-700 rounded">
                                {{ __('Unauthorized action') }}
                            </div>
                        @endif
                        @break

                    @case('user_management')
                        @if (Auth::user()->is_admin || Auth::user()->ownsTeam($team) || Auth::user()->hasTeamRole($team, 'admin'))
                            @include('teams.tabs.user-management', ['team' => $team])
                        @else
                            <div class="p-4 bg-red-100 text-red-700 rounded">
                                {{ __('Unauthorized action') }}
                            </div>
                        @endif
                        @break


                    @case('technical_service')
                        @if (Auth::user()->is_admin)
                            @include('teams.tabs.technical-service', ['team' => $team])
                        @else
                            <div class="p-4 bg-red-100 text-red-700 rounded">
                                {{ __('Unauthorized action') }}
                            </div>
                        @endif
                        @break

                    @default
                        @include('teams.tabs.settings', ['team' => $team])
                @endswitch
            </div>
        </div>
    </div>
</x-app-layout>
