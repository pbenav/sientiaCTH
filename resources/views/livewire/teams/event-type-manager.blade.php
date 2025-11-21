<div>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Tipos de evento') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Aquí puedes gestionar los tipos de evento de tu equipo.') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-6 overflow-hidden border-b border-gray-200 sm:rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Nombre') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Color') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Día Completo') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Jornada Principal') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Autorizable') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Acciones') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($eventTypes as $eventType)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $eventType->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full shadow-sm border border-gray-200" style="background-color: {{ $eventType->color }}"></div>
                                        <span class="ml-2 text-xs text-gray-500">{{ $eventType->color }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($eventType->is_all_day)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('Sí') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($eventType->is_workday_type)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Sí') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($eventType->is_authorizable)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ __('Sí') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($isTeamAdmin)
                                        <button wire:click="manageEventType({{ $eventType->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors duration-150">
                                            {{ __('Editar') }}
                                        </button>
                                        <button wire:click="confirmEventTypeDeletion({{ $eventType->id }})" class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                            {{ __('Eliminar') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($isTeamAdmin)
                <div class="mt-6 flex items-center justify-end">
                    <x-jet-button wire:click="manageEventType" class="bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-900 focus:border-indigo-900 ring-indigo-300">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
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
            {{ __('¿Estás seguro de que quieres eliminar este tipo de evento? Esta acción no se puede deshacer.') }}
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
            <div class="flex items-center space-x-3 border-b border-gray-100 pb-4">
                <div class="bg-indigo-100 p-2 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">
                    {{ isset($state['id']) ? __('Editar tipo de evento') : __('Añadir tipo de evento') }}
                </h3>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-jet-label for="name" value="{{ __('Nombre') }}" class="font-semibold text-gray-700" />
                        <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" placeholder="Ej. Vacaciones" />
                        <x-jet-input-error for="state.name" class="mt-2" />
                    </div>
                    <div>
                        <x-jet-label for="color" value="{{ __('Color') }}" class="font-semibold text-gray-700" />
                        <div class="mt-1 flex items-center space-x-3">
                            <x-jet-input id="color" type="color" class="h-10 w-20 p-1 rounded-md border border-gray-300 cursor-pointer" wire:model.defer="state.color" />
                            <span class="text-sm text-gray-500">{{ $state['color'] ?? '' }}</span>
                        </div>
                        <x-jet-input-error for="state.color" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-jet-label for="observations" value="{{ __('Observaciones') }}" class="font-semibold text-gray-700" />
                    <textarea id="observations" 
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm transition duration-150 ease-in-out sm:text-sm" 
                              wire:model.defer="state.observations"
                              rows="3"
                              placeholder="Descripción opcional..."></textarea>
                    <x-jet-input-error for="state.observations" class="mt-2" />
                </div>

                <div class="bg-gray-50 rounded-lg p-4 space-y-4 border border-gray-100">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <x-jet-checkbox id="is_all_day" wire:model.defer="state.is_all_day" class="text-indigo-600" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_all_day" class="font-medium text-gray-700">{{ __('Evento de día completo') }}</label>
                            <p class="text-gray-500">{{ __('Marca esto si el evento dura todo el día.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <x-jet-checkbox id="is_workday_type" wire:model.defer="state.is_workday_type" class="text-indigo-600" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_workday_type" class="font-medium text-gray-700">{{ __('Tipo de Jornada Principal') }}</label>
                            <p class="text-gray-500">{{ __('Indica si este evento cuenta como jornada laboral principal.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <x-jet-checkbox id="is_authorizable" wire:model.defer="state.is_authorizable" class="text-indigo-600" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_authorizable" class="font-medium text-gray-700">{{ __('Autorizable') }}</label>
                            <p class="text-gray-500">{{ __('Requiere aprobación de un administrador.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('managingEventType')" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2 bg-indigo-600 hover:bg-indigo-700" wire:click="saveEventType" wire:loading.attr="disabled">
                {{ __('Guardar') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
