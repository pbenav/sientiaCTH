<div>
    @if ($isValidToken)
        <!-- Info Alert -->
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm text-blue-700">
                        {{ __('Please, enter the start and end times of your workday.') }}
                    </p>
                    <p class="mt-2 text-sm text-blue-700 font-medium">
                        {{ __('This exceptional clock-in requires administrator approval.') }}
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save">
            <!-- Main Form Grid (2 columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Column: Date & Time -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Date and Time') }}</h4>
                        
                        <div class="space-y-4">
                            <div>
                                <x-jet-label value="{{ __('Start') }}" class="font-medium text-gray-700 mb-1" />
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="date" 
                                               id="start_date" 
                                               wire:model.defer="start_date" 
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                                        @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="time" 
                                               id="start_time" 
                                               wire:model.defer="start_time" 
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                                        @error('start_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <x-jet-label value="{{ __('End') }}" class="font-medium text-gray-700 mb-1" />
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="date" 
                                               id="end_date" 
                                               wire:model.defer="end_date" 
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                                        @error('end_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="time" 
                                               id="end_time" 
                                               wire:model.defer="end_time" 
                                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm">
                                        @error('end_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Observations -->
                <div class="space-y-6">
                    <div>
                        <x-jet-label for="observations" value="{{ __('Reason for exceptional clock-in') }}" class="font-medium text-gray-700" />
                        <textarea id="observations"
                                  wire:model.defer="observations"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm"
                                  placeholder="{{ __('exceptional_event.reason_placeholder') }}"
                                  rows="8"></textarea>
                        @error('observations') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        <p class="mt-1 text-xs text-gray-500">{{ __('Explain why you need to clock in outside your regular schedule') }}</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex justify-end">
                <x-jet-button type="submit" class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500">
                    {{ __('Save') }}
                </x-jet-button>
            </div>
        </form>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">{{ __('Invalid or Expired Link') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ session('error') ?: __('This link is not valid or has expired.') }}</p>
            <div class="mt-6">
                <a href="{{ route('events') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Go to Events') }}
                </a>
            </div>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="mt-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-md">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif
</div>
