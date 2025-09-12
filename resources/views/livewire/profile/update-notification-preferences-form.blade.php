<x-jet-form-section submit="update">
    <x-slot name="title">
        Preferencias de Notificaciones
    </x-slot>

    <x-slot name="description">
        Configura cómo quieres recibir las notificaciones de nuevos mensajes.
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <label for="notify_new_messages" class="flex items-center">
                <x-jet-checkbox id="notify_new_messages" wire:model.defer="notifyNewMessages" />
                <span class="ml-2 text-sm text-gray-600">Mostrar una alerta al recibir un nuevo mensaje.</span>
            </label>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            Guardado.
        </x-jet-action-message>

        <x-jet-button>
            Guardar
        </x-jet-button>
    </x-slot>
</x-jet-form-section>
