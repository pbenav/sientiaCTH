<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <!-- Flex layout: 1/4 for clock-in, 3/4 for announcements -->
            <div class="flex flex-col md:flex-row gap-6">
                
                <!-- Smart Clock Interface - 1/4 width (left) -->
                <div class="w-full md:flex-none md:w-1/4">
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
                
                <!-- Team Announcements - 3/4 width (right) -->
                <div class="w-full md:flex-1">
                    @livewire('team-announcements')
                </div>

            </div>
        </div>
    </div>
</x-app-layout>