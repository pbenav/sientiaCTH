<x-jet-form-section submit="updateTimezone">
    <x-slot name="title">
        {{ __('Zona Horaria del Equipo') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Selecciona la zona horaria principal para tu equipo. Todas las fechas y horas se mostrarán en esta zona horaria.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="timezone" value="{{ __('Zona Horaria') }}" />
            
            @if($isTeamAdmin)
                <select id="timezone" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model.defer="state.timezone">
                    <option value="">{{ __('Selecciona una zona horaria') }}</option>
                    @foreach ($timezones as $identifier => $name)
                        <option value="{{ $identifier }}">{{ $name }}</option>
                    @endforeach
                </select>
            @else
                <div class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700">
                    {{ $timezones[$state['timezone']] ?? $state['timezone'] ?? __('No configurada') }}
                </div>
            @endif
            
            <x-jet-input-error for="timezone" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Guardado.') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('Guardar') }}
            </x-jet-button>
        </x-slot>
    @endif
</x-jet-form-section>
