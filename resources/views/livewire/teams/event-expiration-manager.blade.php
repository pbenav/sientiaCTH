<x-jet-form-section submit="updateEventExpiration">
    <x-slot name="title">
        {{ __('Automatic Event Closure') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Set the number of days after which open events should be automatically closed. Leave blank to disable.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="event_expiration_days" value="{{ __('Expiration Days') }}" />
            <x-jet-input id="event_expiration_days" type="number" class="mt-1 block w-full" wire:model.defer="state.event_expiration_days" />
            <x-jet-input-error for="state.event_expiration_days" class="mt-2" />
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
