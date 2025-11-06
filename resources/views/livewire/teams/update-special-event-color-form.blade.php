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
            <x-jet-input id="special_event_color" type="color" class="mt-1 block w-full" wire:model.defer="state.special_event_color" />
            <x-jet-input-error for="special_event_color" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>