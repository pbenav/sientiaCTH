<div>
    <x-jet-form-section submit="updateClockInDelaySettings">
        <x-slot name="title">
            {{ __('Fichaje con Demora Forzada') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Activa esta opción para forzar un margen máximo de minutos para fichar antes o después de la hora programada. Si un usuario excede este margen, podrá solicitar un enlace de fichaje excepcional.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Enable/Disable Feature -->
            <div class="col-span-6 sm:col-span-4">
                <label for="force_clock_in_delay" class="flex items-center">
                    @if(\Illuminate\Support\Facades\Gate::check('update', $team))
                        <x-jet-checkbox id="force_clock_in_delay" wire:model.defer="state.force_clock_in_delay" />
                    @else
                        <x-jet-checkbox id="force_clock_in_delay" wire:model.defer="state.force_clock_in_delay" disabled />
                    @endif
                    <span class="ml-2 text-sm text-gray-600">{{ __('Activar Fichaje con Demora Forzada') }}</span>
                </label>
            </div>

            @if (isset($state['force_clock_in_delay']) && $state['force_clock_in_delay'])
                <!-- Clock-in Delay Minutes -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="clock_in_delay_minutes" value="{{ __('Margen de Minutos para Fichar') }}" />
                    <x-jet-input id="clock_in_delay_minutes" type="number" class="mt-1 block w-full" wire:model.defer="state.clock_in_delay_minutes" />
                    <x-jet-input-error for="clock_in_delay_minutes" class="mt-2" />
                    <span class="mt-1 text-xs text-gray-500">
                        {{ __('Número de minutos antes y después de la hora de inicio/fin programada que el usuario tiene permitido fichar. Por ejemplo, si el margen es de 15 minutos, un usuario programado para empezar a las 08:00 podrá fichar entre las 07:45 y las 08:15.') }}
                    </span>
                </div>

                <!-- Clock-in Grace Period Minutes -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="clock_in_grace_period_minutes" value="{{ __('Periodo de Gracia para Fichaje Excepcional (Minutos)') }}" />
                    <x-jet-input id="clock_in_grace_period_minutes" type="number" class="mt-1 block w-full" wire:model.defer="state.clock_in_grace_period_minutes" />
                    <x-jet-input-error for="clock_in_grace_period_minutes" class="mt-2" />
                    <span class="mt-1 text-xs text-gray-500">
                        {{ __('Si un usuario supera el margen, se le enviará un enlace para fichar. Este enlace será válido durante el número de minutos que definas aquí.') }}
                    </span>
                </div>
            @endif
        </x-slot>

        @if (Gate::check('update', $team))
            <x-slot name="actions">
                <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
                    {{ __('Guardar') }}
                </x-jet-button>
            </x-slot>
        @endif
    </x-jet-form-section>

    <x-jet-section-border />

    <x-jet-form-section submit="updateClockInDelaySettings">
        <x-slot name="title">
            {{ __('Tiempo Máximo de Jornada') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Define un límite máximo de horas para la jornada laboral diaria. Si un trabajador supera este límite al fichar la salida, se le solicitará que realice un ajuste.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Max Workday Duration -->
            <div class="col-span-6 sm:col-span-4">
                <label for="force_max_workday_duration" class="flex items-center">
                    @if(\Illuminate\Support\Facades\Gate::check('update', $team))
                        <x-jet-checkbox id="force_max_workday_duration" wire:model.defer="state.force_max_workday_duration" />
                    @else
                        <x-jet-checkbox id="force_max_workday_duration" wire:model.defer="state.force_max_workday_duration" disabled />
                    @endif
                    <span class="ml-2 text-sm text-gray-600">{{ __('Forzar Tiempo Máximo de Jornada') }}</span>
                </label>
            </div>

            @if (isset($state['force_max_workday_duration']) && $state['force_max_workday_duration'])
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="max_workday_duration_minutes" value="{{ __('Tiempo Máximo de Jornada (Minutos)') }}" />
                    <x-jet-input id="max_workday_duration_minutes" type="number" class="mt-1 block w-full" wire:model.defer="state.max_workday_duration_minutes" />
                    <x-jet-input-error for="max_workday_duration_minutes" class="mt-2" />
                    <span class="mt-1 text-xs text-gray-500">
                        {{ __('Define el número máximo de minutos que un trabajador puede fichar en un día. Si se supera, se le pedirá ajustar el fichaje.') }}
                    </span>
                </div>
            @endif
        </x-slot>

        @if (Gate::check('update', $team))
            <x-slot name="actions">
                <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
                    {{ __('Guardar') }}
                </x-jet-button>
            </x-slot>
        @endif
    </x-jet-form-section>

    <x-jet-section-border />

    <x-jet-form-section submit="updateEventExpirationSettings">
        <x-slot name="title">
            {{ __('Cierre Automático de Eventos no Confirmados') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Define el número de días que deben pasar antes de que un evento no confirmado sea cerrado automáticamente. Deja el campo en blanco para desactivar esta función.') }}
        </x-slot>

        <x-slot name="form">
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="event_expiration_days" value="{{ __('Días de Expiración de Eventos') }}" />
                <x-jet-input id="event_expiration_days" type="number" class="mt-1 block w-full" wire:model.defer="state.event_expiration_days" />
                <x-jet-input-error for="event_expiration_days" class="mt-2" />
            </div>
        </x-slot>

        @if (Gate::check('update', $team))
            <x-slot name="actions">
                <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
                    {{ __('Guardar') }}
                </x-jet-button>
            </x-slot>
        @endif
    </x-jet-form-section>
</div>

<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('saved', function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ __("Cambios guardados correctamente") }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    });
</script>
