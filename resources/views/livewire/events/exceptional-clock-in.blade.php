<div>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
            @if ($isValidToken)
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('Regularize Clock-in') }}</h2>
                <p class="text-center text-gray-600 mb-6">{{ __('Please, enter the start and end times of your workday.') }}</p>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                            <input type="date" id="start_date" wire:model.defer="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">{{ __('Start Time') }}</label>
                            <input type="time" id="start_time" wire:model.defer="start_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
                            <input type="date" id="end_date" wire:model.defer="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">{{ __('End Time') }}</label>
                            <input type="time" id="end_time" wire:model.defer="end_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="observations" class="block text-sm font-medium text-gray-700">{{ __('Reason for exceptional clock-in') }}</label>
                        <textarea id="observations" wire:model.defer="observations" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        @error('observations') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">{{ __('Save') }}</button>
                    </div>
                </form>
            @else
                <div class="text-center">
                    <p class="text-red-500">{{ session('error') ?: __('This link is not valid or has expired.') }}</p>
                    <a href="{{ route('events') }}" class="text-indigo-600 hover:underline mt-4 inline-block">{{ __('Go to Events') }}</a>
                </div>
            @endif

            @if(session()->has('success'))
                <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
