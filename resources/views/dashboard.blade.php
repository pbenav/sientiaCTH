<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                <!-- Smart Clock Interface for authenticated users -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Clock In/Out') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Manage your work time registration') }}</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ now()->format('l, F j, Y') }}
                        </div>
                    </div>
                    
                    @livewire('smart-clock-button')
                </div>

            </div>
        </div>
    </div>
</x-app-layout>