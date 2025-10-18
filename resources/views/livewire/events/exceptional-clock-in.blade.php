<div>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>

        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                {{ session('error') }}
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">{{ __('Go to Dashboard') }}</a>
        @elseif (session('success'))
             <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
            <a href="{{ route('events') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">{{ __('Go to Events') }}</a>
        @else
            @if ($isValidToken)
                <form wire:submit.prevent="save">
                    <h2 class="text-2xl font-bold text-center mb-4">{{ __('Regularize Clock-in') }}</h2>
                    <p class="mb-4">{{ __('Please, enter the start and end times for your workday.') }}</p>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Start Date -->
                        <div>
                            <x-jet-label for="start_date" value="{{ __('Start Date') }}" />
                            <x-jet-input id="start_date" type="date" class="mt-1 block w-full" wire:model.defer="start_date" />
                            <x-jet-input-error for="start_date" class="mt-2" />
                        </div>

                        <!-- Start Time -->
                        <div>
                            <x-jet-label for="start_time" value="{{ __('Start Time') }}" />
                            <x-jet-input id="start_time" type="time" class="mt-1 block w-full" wire:model.defer="start_time" />
                             <x-jet-input-error for="start_time" class="mt-2" />
                        </div>

                        <!-- End Date -->
                        <div>
                            <x-jet-label for="end_date" value="{{ __('End Date') }}" />
                            <x-jet-input id="end_date" type="date" class="mt-1 block w-full" wire:model.defer="end_date" />
                             <x-jet-input-error for="end_date" class="mt-2" />
                        </div>

                        <!-- End Time -->
                        <div>
                            <x-jet-label for="end_time" value="{{ __('End Time') }}" />
                            <x-jet-input id="end_time" type="time" class="mt-1 block w-full" wire:model.defer="end_time" />
                            <x-jet-input-error for="end_time" class="mt-2" />
                        </div>
                    </div>

                    <!-- Observations -->
                    <div class="mt-4">
                        <x-jet-label for="observations" value="{{ __('Reason for exceptional clock-in') }}" />
                        <textarea id="observations" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" wire:model.defer="observations"></textarea>
                        <x-jet-input-error for="observations" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-jet-button>
                            {{ __('Save') }}
                        </x-jet-button>
                    </div>
                </form>
            @else
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    {{ __('This link is invalid or has expired.') }}
                </div>
                 <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">{{ __('Go to Dashboard') }}</a>
            @endif
        @endif
    </x-jet-authentication-card>
</div>
