<div>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Días Festivos') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Gestiona los días festivos de tu equipo.') }}
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
                                {{ __('Fecha') }}
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Tipo') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Acciones') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($holidays as $holiday)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $holiday->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $holiday->date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $holiday->type }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($isTeamAdmin)
                                        <button wire:click="manageHoliday({{ $holiday->id }})"
                                            class="text-indigo-600 hover:text-indigo-900">{{ __('Editar') }}</button>
                                        <button wire:click="confirmHolidayDeletion({{ $holiday->id }})"
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
                    <x-jet-button wire:click="manageHoliday">
                        {{ __('Añadir Festivo') }}
                    </x-jet-button>
                </div>
            @endif
        </x-slot>
    </x-jet-action-section>

    <!-- Delete Holiday Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingHolidayDeletion">
        <x-slot name="title">
            {{ __('Eliminar Festivo') }}
        </x-slot>

        <x-slot name="content">
            {{ __('¿Estás seguro de que quieres eliminar este día festivo?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingHolidayDeletion')" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="deleteHoliday" wire:loading.attr="disabled">
                {{ __('Eliminar') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>

    <!-- Manage Holiday Modal -->
    <x-jet-dialog-modal wire:model="managingHoliday">
        <x-slot name="title">
            {{ $holidayId ? __('Editar Festivo') : __('Añadir Festivo') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-jet-label for="name" value="{{ __('Nombre') }}" />
                    <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="name" />
                    <x-jet-input-error for="name" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="date" value="{{ __('Fecha') }}" />
                    <x-jet-input id="date" type="date" class="mt-1 block w-full" wire:model.defer="date" />
                    <x-jet-input-error for="date" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="type" value="{{ __('Tipo') }}" />
                    <x-jet-input id="type" type="text" class="mt-1 block w-full" wire:model.defer="type" />
                    <x-jet-input-error for="type" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('managingHoliday')" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="saveHoliday" wire:loading.attr="disabled">
                {{ __('Guardar') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
