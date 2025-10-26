<x-jet-form-section submit="updateIrregularEventColor">
    <x-slot name="title">
        {{ __('Color para Eventos Irregulares') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Selecciona un color para resaltar los eventos irregulares en el calendario.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="color" value="{{ __('Color') }}" />
            <x-jet-input id="color" type="color" class="mt-1 block w-full" wire:model.defer="state.irregular_event_color" />
            <x-jet-input-error for="color" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Guardado.') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('Guardar') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>
