<x-jet-form-section submit="updateIrregularEventColor">
    <x-slot name="title">
        {{ __('Irregular Event Color') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Choose a color for irregular events. This color will be used for events that are not exceptional, but do not belong to the main workday type.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="irregular_event_color" value="{{ __('Color') }}" />
            <x-jet-input id="irregular_event_color" type="color" class="mt-1 block w-full" wire:model.defer="state.irregular_event_color" />
            <x-jet-input-error for="irregular_event_color" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    @endif
</x-jet-form-section>
