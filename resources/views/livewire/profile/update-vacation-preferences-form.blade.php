<x-jet-form-section submit="updateVacationPreferences">
    <x-slot name="title">
        {{ __('Vacaciones') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Configure cómo se calculan sus días de vacaciones en las estadísticas.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Calculation Type -->
        <div class="col-span-6" x-data="{ calculationType: @entangle('state.vacation_calculation_type') }">
            <x-jet-label for="vacation_calculation_type" value="{{ __('Tipo de cálculo') }}" />
            
            <div class="mt-3 space-y-3">
                <!-- Natural Days Option -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input 
                            id="calculation_natural" 
                            type="radio" 
                            value="natural"
                            wire:model.defer="state.vacation_calculation_type"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                        >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="calculation_natural" class="font-medium text-gray-700 cursor-pointer">
                            {{ __('Días naturales (30 días de calendario)') }}
                        </label>
                        <p class="text-gray-500">
                            {{ __('Se cuentan todos los días del calendario, incluyendo fines de semana y festivos.') }}
                        </p>
                    </div>
                </div>

                <!-- Working Days Option -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input 
                            id="calculation_working" 
                            type="radio" 
                            value="working"
                            wire:model.defer="state.vacation_calculation_type"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                        >
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="calculation_working" class="font-medium text-gray-700 cursor-pointer">
                            {{ __('Días hábiles (excluyendo fines de semana y festivos)') }}
                        </label>
                        <p class="text-gray-500">
                            {{ __('Se excluyen sábados, domingos y festivos del equipo.') }}
                        </p>
                    </div>
                </div>
            </div>

            <x-jet-input-error for="state.vacation_calculation_type" class="mt-2" />
        </div>

        <!-- Working Days Count (shown only when working days is selected) -->
        <div class="col-span-6 sm:col-span-4" x-data="{ calculationType: @entangle('state.vacation_calculation_type') }" x-show="calculationType === 'working'" x-transition>
            <x-jet-label for="vacation_working_days" value="{{ __('Número de días hábiles') }}" />
            <input 
                id="vacation_working_days" 
                type="number" 
                min="1" 
                max="365"
                class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" 
                wire:model.defer="state.vacation_working_days"
            >
            <x-jet-input-error for="state.vacation_working_days" class="mt-2" />
            <p class="mt-2 text-sm text-gray-600">
                {{ __('Introduzca el número de días hábiles de vacaciones por año (por defecto 22).') }}
            </p>
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
