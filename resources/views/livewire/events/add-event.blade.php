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
                <p class="p-2">En esta nueva versión <strong>debes elegir el tipo de evento</strong>, que no podrá ser modificado una vez creado. ¡Elige bien!</p>
            </div>

            <div class="mb-2">
                <x-jet-label value="{{ __('Event Type') }}" class="required" />
                <select class="sl-select" required wire:model.live="event_type_id" name="event_type_id">
                    <option value="">{{ __('Select an option') }}</option>
                    @foreach($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='event_type_id' />
            </div>

            @php
                $defaultWorkCenterMeta = auth()->user()->meta->where('meta_key', 'default_work_center_id')->first();
                if ($defaultWorkCenterMeta) {
                    $defaultWorkCenter = auth()->user()->currentTeam->workCenters()->find($defaultWorkCenterMeta->meta_value);
                }
            @endphp

            @if(isset($defaultWorkCenter))
                <div class="mb-2">
                    <x-jet-label value="{{ __('Default Work Center') }}" />
                    <p class="text-sm text-gray-700">
                        {{ $defaultWorkCenter->name }}
                    </p>
                </div>
            @endif

            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2 required" />
                <x-jet-input type="date" class="mr-2" wire:model.defer="start_date" />
                <x-jet-input-error for="start_date" />

                @if ($selectedEventType && !$selectedEventType->is_all_day)
                    <x-jet-input type="time" class="" wire:model.defer="start_time" />
                    <x-jet-input-error for="start_time" />
                @endif
            </div>

            @if ($selectedEventType && $selectedEventType->is_all_day)
                <div class="mb-4">
                    <x-jet-label value="{{ __('End date') }}" class="mt-3 mr-2 required" />
                    <x-jet-input type="date" class="mr-2" wire:model.defer="end_date" />
                    <x-jet-input-error for="end_date" />
                </div>
            @endif
            <div class="text-sm text-gray-500">{{ $workScheduleHint }}</div>

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
