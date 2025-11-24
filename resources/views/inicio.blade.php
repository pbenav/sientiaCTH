<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                {{-- Clock-in Widget - Left Column (spans 1 column on large screens) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg h-full">
                        <div class="p-6">
                            <div class="mb-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('Clock In/Out') }}</h3>
                                    <p class="text-sm text-gray-600">{{ __('Manage your work time registration') }}</p>
                                </div>
                                <div class="text-sm text-gray-500 mt-3">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y') }}
                                </div>
                            </div>
                            @livewire('smart-clock-button')
                        </div>
                    </div>
                </div>
                
                {{-- Dashboard Content - Right Column (spans 3 columns on large screens) --}}
                <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6 auto-rows-min">
                    
                    {{-- Announcements Section - spans 2 columns on medium+ screens --}}
                    <div class="md:col-span-2 bg-white rounded-lg shadow-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Team Announcements') }}</h3>
                        </div>
                        <div class="max-h-[30vh] overflow-y-auto" style="scrollbar-width: thin;">
                            @livewire('team-announcements')
                        </div>
                    </div>

                    {{-- Inbox Summary - 1 column --}}
                    <div>
                        @livewire('inbox-summary-component')
                    </div>

                    {{-- Stats Cards - 1 column (will create internal 2x2 grid) --}}
                    <div>
                        @livewire('dashboard-stats-component')
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>