<x-jet-form-section submit="updateEventExpiration">
    <x-slot name="title">
        {{ __('Caducidad de Eventos') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Define el número de días que un evento puede permanecer sin confirmar antes de ser considerado caducado y procesado por el sistema de autocierre.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="event_expiration_days" value="{{ __('Días para la caducidad de eventos no confirmados') }}" />
            <x-jet-input id="event_expiration_days" type="number" class="mt-1 block w-full" wire:model.defer="state.event_expiration_days" />
            <x-jet-input-error for="state.event_expiration_days" class="mt-2" />
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
