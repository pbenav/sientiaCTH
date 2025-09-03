<div>
    <x-jet-dialog-modal wire:model="showAddEventModal">

        <x-slot name="title">
            {{ __('Add new event') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-4 bg-green-200">
                <p class="p-2">La <strong>función del registro</strong> horario es la de poder demostrar la hora de entrada y salida del puesto de trabajo.
                   No tiene mucho sentido fichar un día antes o un día después.
                   <br />Por favor, acostúmbrate a hacerlo a la hora correcta. <br>
                <strong>¡Muchas gracias!</strong></p>
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2 required" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='start_date' />
                <x-jet-input-error for='start_date' />
                <x-jet-input type="time" class="" wire:model.defer='start_time' />
                <x-jet-input-error for='start_time' />
                <div class="text-sm text-gray-500">{{ $workScheduleHint }}</div>
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Description') }}" class="required" />
                <select class="sl-select" required wire:model="description" name="description">
                    <option value="{{ __('Workday') }}" selected="selected">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>

            <div class="mx-auto mb-4">
                <x-jet-label value="{{ __('Observations') }}" />
                <textarea class="w-full form-control"                
                wire:model="observations"
                 rows="4"
                 placeholder="{{ __('Observations') }}"
                 name="observations"
                 id="observations"
                 maxlength="255"></textarea>
                <x-jet-input-error for='observations' />
            </div>

            <div class="mb-4">
                <input type="hidden" id="user_id" name="user_id" wire:model.defer="user_id">
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="cancel">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            {{-- Function save('') empty parameter to say that we are already in dashboard --}}
            <x-jet-button wire:click="save('')" wire:loading.attr="disabled" class="justify-center ml-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-500"
                wire_target="save">
                {{ __('Create Event') }}
            </x-jet-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
