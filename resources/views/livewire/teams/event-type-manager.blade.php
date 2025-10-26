<div>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Tipos de evento') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Aquí puedes gestionar los tipos de evento de tu equipo.') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Nombre') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Color') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Evento diario') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Tipo de Jornada Principal') }}
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
                                    <div class="w-6 h-6 rounded-full" style="background-color: {{ $eventType->color }}">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <input type="checkbox"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            @if ($eventType->is_all_day) checked @endif onclick="return false;" />
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <input type="checkbox"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            @if ($eventType->is_workday_type) checked @endif onclick="return false;" />
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($isTeamAdmin)
                                        <button wire:click="manageEventType({{ $eventType->id }})"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('Editar') }}</button>
                                        <button wire:click="confirmEventTypeDeletion({{ $eventType->id }})"
                                            class="ml-2 text-red-600 hover:text-red-900">{{ __('Eliminar') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($isTeamAdmin)
                <div class="mt-6 flex items-center justify-end">
                    <x-jet-button wire:click="manageEventType">
                        {{ __('Añadir tipo de evento') }}
                    </x-jet-button>
                </div>
            @endif
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
            {{ isset($state['id']) ? __('Editar tipo de evento') : __('Añadir tipo de evento') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-jet-label for="name" value="{{ __('Nombre') }}" />
                    <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" />
                    <x-jet-input-error for="state.name" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="color" value="{{ __('Color') }}" />
                    <x-jet-input id="color" type="color" class="mt-1 block w-full" wire:model.defer="state.color" />
                    <x-jet-input-error for="state.color" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="observations" value="{{ __('Observaciones') }}" />
                    <textarea id="observations" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model.defer="state.observations"></textarea>
                    <x-jet-input-error for="state.observations" class="mt-2" />
                </div>
                <div class="flex items-center">
                    <x-jet-checkbox id="is_all_day" wire:model.defer="state.is_all_day" />
                    <x-jet-label for="is_all_day" class="ml-2" value="{{ __('Evento de día completo') }}" />
                    <x-jet-input-error for="state.is_all_day" class="mt-2" />
                </div>
                <div class="flex items-center">
                    <x-jet-checkbox id="is_workday_type" wire:model.defer="state.is_workday_type" />
                    <x-jet-label for="is_workday_type" class="ml-2" value="{{ __('Tipo de Jornada Principal') }}" />
                    <x-jet-input-error for="state.is_workday_type" class="mt-2" />
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
