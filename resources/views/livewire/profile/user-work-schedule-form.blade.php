<x-jet-form-section submit="save">
    <x-slot name="title">
        {{ __('Horario Laboral') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Define los tramos horarios de tu jornada laboral. Esto ayudará a que el sistema te sugiera la hora de entrada y salida al crear un nuevo evento.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
        </div>

        @foreach ($schedule as $index => $item)
            <div class="col-span-6 sm:col-span-2">
                <x-jet-label for="start_time_{{ $index }}" value="{{ __('Hora de inicio') }}" />
                <x-jet-input id="start_time_{{ $index }}" type="time" class="mt-1 block w-full" wire:model.defer="schedule.{{ $index }}.start" :disabled="$editingIndex !== $index" />
                <x-jet-input-error for="schedule.{{ $index }}.start" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-2">
                <x-jet-label for="end_time_{{ $index }}" value="{{ __('Hora de fin') }}" />
                <x-jet-input id="end_time_{{ $index }}" type="time" class="mt-1 block w-full" wire:model.defer="schedule.{{ $index }}.end" :disabled="$editingIndex !== $index" />
                <x-jet-input-error for="schedule.{{ $index }}.end" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-1">
                <x-jet-label value="{{ __('Días') }}" />
                <div class="mt-2 flex space-x-4">
                    @foreach(['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $day)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="schedule.{{ $index }}.days" value="{{ $day }}" class="form-checkbox h-5 w-5 text-indigo-600" :disabled="$editingIndex !== $index">
                            <span class="ml-2 text-gray-700">{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="col-span-6 sm:col-span-1 flex items-end space-x-2">
                @if ($editingIndex === $index)
                    <x-jet-secondary-button type="button" wire:click="cancelEdit">
                        {{ __('Cancelar') }}
                    </x-jet-secondary-button>
                @else
                    <x-jet-button type="button" wire:click="editScheduleRow({{ $index }})">
                        {{ __('Editar') }}
                    </x-jet-button>
                @endif
                <x-jet-danger-button type="button" wire:click="removeScheduleRow({{ $index }})">
                    {{ __('Eliminar') }}
                </x-jet-danger-button>
            </div>
        @endforeach

        <div class="col-span-6 text-right">
            <x-jet-secondary-button type="button" wire:click="addScheduleRow">
                {{ __('Añadir tramo') }}
            </x-jet-secondary-button>
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
