<div>
    <!-- Event detail. Modal table -->
    <x-jet-dialog-modal wire:model="showModalEditEvent">

        <x-slot name='title'>
            {{ __('Edit event') }}: <span>{{ $event->id }}</span>
            <p>{{ __('Worker')}}: {{ $user->name }} {{ $user->family_name1 }}</p>
        </x-slot>

        <x-slot name='content'>
            {{-- New Datepicker HTML5 --}}
            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" />
                <input type="datetime-local" wire:model="event.start" />
                <x-jet-input-error for='event.start' />
                <div class="text-sm text-gray-500">{{ $workScheduleHint }}</div>
            </div>
            <div class="mb-4">
                <x-jet-label value="{{ __('End date') }}" />
                <input type="datetime-local" wire:model="event.end" min="{{ $event->start }}" />
                <x-jet-input-error for='event.end' />
            </div>
            {{-- end-datepicker --}}

            <div class="mb-4">
                <x-jet-label value="{{ __('Event Type') }}" />
                <select class="sl-select" wire:model.defer="event.event_type_id" name="event.event_type_id" required>
                    @foreach($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='event.event_type_id' />
            </div>

            <div class="mx-auto mb-4">
                <x-jet-label value="{{ __('Observations') }}" />
                <textarea class="w-full form-control"
                 wire:model.defer="event.observations"
                 rows="4"
                 placeholder="{{ __('Observations') }}"
                 name="observations"
                 id="observations"
                 maxlength="255"></textarea>
                <x-jet-input-error for='event.observations' />
            </div>

        </x-slot>

        <x-slot name='footer'>
            <x-jet-secondary-button wire:click="$set('showModalEditEvent', false)" wire:target="GetTimeRegisters">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button wire:click="update" wire:loading.attr="disabled"
                class="justify-center ml-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-500">
                {{ __('Update event') }}
            </x-jet-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
