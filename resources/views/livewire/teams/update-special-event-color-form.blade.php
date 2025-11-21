<x-jet-form-section submit="updateSpecialEventColor">
    <x-slot name="title">
        {{ __('Special Event Color') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Choose a color for special events. This color will be used for exceptional events and events that do not have a specific type color.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="special_event_color" value="{{ __('Color') }}" />
            <div class="mt-1 flex items-center space-x-3">
                <div class="relative">
                    <x-jet-input id="special_event_color" type="color" class="h-10 w-20 p-1 rounded-md border border-gray-300 cursor-pointer" wire:model.defer="state.special_event_color" />
                </div>
                <div class="text-sm text-gray-500 font-mono bg-gray-50 px-2 py-1 rounded border border-gray-200">
                    {{ $state['special_event_color'] ?? '#000000' }}
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                {{ __('Click the color box to select a new color.') }}
            </p>
            <x-jet-input-error for="special_event_color" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>