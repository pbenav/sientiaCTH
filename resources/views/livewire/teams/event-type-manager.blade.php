<div>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Tipos de evento') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Aquí puedes gestionar los tipos de evento de tu equipo.') }}
        </x-slot>

        <x-slot name="content">
            <div class="flex items-center justify-end">
                <x-jet-button wire:click="manageEventType">
                    {{ __('Añadir tipo de evento') }}
                </x-jet-button>
            </div>

            <div class="mt-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Nombre') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Color') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Acciones') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($eventTypes as $eventType)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $eventType->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-6 h-6 rounded-full" style="background-color: {{ $eventType->color }}"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($isTeamAdmin)
                                        <button wire:click="manageEventType({{ $eventType->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('Editar') }}</button>
                                        <button wire:click="confirmEventTypeDeletion({{ $eventType->id }})" class="ml-2 text-red-600 hover:text-red-900">{{ __('Eliminar') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot>
    </x-jet-action-section>

    <!-- Delete Event Type Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingEventTypeDeletion">
        <x-slot name="title">
            {{ __('Eliminar tipo de evento') }}
        </x-slot>

        <x-slot name="content">
            {{ __('¿Estás seguro de que quieres eliminar este tipo de evento?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingEventTypeDeletion')" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="deleteEventType" wire:loading.attr="disabled">
                {{ __('Eliminar') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>

    <!-- Manage Event Type Modal -->
    <x-jet-dialog-modal wire:model="managingEventType">
        <x-slot name="title">
            {{ isset($this->eventType->id) ? __('Editar tipo de evento') : __('Añadir tipo de evento') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-jet-label for="name" value="{{ __('Nombre') }}" />
                    <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="eventType.name" />
                    <x-jet-input-error for="eventType.name" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="color" value="{{ __('Color') }}" />
                    <x-jet-input id="color" type="color" class="mt-1 block w-full" wire:model.defer="eventType.color" />
                    <x-jet-input-error for="eventType.color" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="observations" value="{{ __('Observaciones') }}" />
                    <textarea id="observations" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model.defer="eventType.observations"></textarea>
                    <x-jet-input-error for="eventType.observations" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('managingEventType')" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="saveEventType" wire:loading.attr="disabled">
                {{ __('Guardar') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
