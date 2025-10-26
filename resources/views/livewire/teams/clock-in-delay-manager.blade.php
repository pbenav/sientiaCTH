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
                    <x-jet-checkbox id="force_clock_in_delay" wire:model.defer="state.force_clock_in_delay" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Activar Fichaje con Demora Forzada') }}</span>
                </label>
            </div>

            @if (isset($state['force_clock_in_delay']) && $state['force_clock_in_delay'])
                <!-- Clock-in Delay Minutes -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="clock_in_delay_minutes" value="{{ __('Margen de Minutos para Fichar') }}" />
                    <x-jet-input id="clock_in_delay_minutes" type="number" class="mt-1 block w-full" wire:model.defer="state.clock_in_delay_minutes" />
                    <x-jet-input-error for="clock_in_delay_minutes" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Número de minutos antes y después de la hora de inicio/fin programada que el usuario tiene permitido fichar. Por ejemplo, si el margen es de 15 minutos, un usuario programado para empezar a las 08:00 podrá fichar entre las 07:45 y las 08:15.') }}
                    </p>
                </div>

                <!-- Clock-in Grace Period Minutes -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="clock_in_grace_period_minutes" value="{{ __('Periodo de Gracia para Fichaje Excepcional (Minutos)') }}" />
                    <x-jet-input id="clock_in_grace_period_minutes" type="number" class="mt-1 block w-full" wire:model.defer="state.clock_in_grace_period_minutes" />
                    <x-jet-input-error for="clock_in_grace_period_minutes" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Si un usuario supera el margen, se le enviará un enlace para fichar. Este enlace será válido durante el número de minutos que definas aquí.') }}
                    </p>
                </div>
            @endif
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
                <x-jet-action-message class="mr-3" on="saved">
                    {{ __('Guardado.') }}
                </x-jet-action-message>

                <x-jet-button>
                    {{ __('Guardar') }}
                </x-jet-button>
            </x-slot>
        @endif
    </x-jet-form-section>
</div>
