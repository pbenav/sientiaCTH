<div>
    <!-- Event detail. Modal table -->
    <x-jet-dialog-modal wire:model="showModalGetTimeRegisters">

        <x-slot name='title'>
            {{ __('Edit event') }}: <span wire:model='event.id'></span>
        </x-slot>

        <x-slot name='content'>
            <div class="mb-4">
                {{-- New Datepicker HTML5 --}}
                 <x-jet-label value="{{ __('Start date') }}" />
                <input type="datetime-local" wire:model="event.start" />
                <x-jet-input-error for='event.start' />
            </div>
            <div class="mb-4">
                <x-jet-label value="{{ __('End date') }}" />
                <input type="datetime-local" wire:model="event.end" min="{{ $event->start }}" />
                <x-jet-input-error for='event.end' />
            </div>
            {{-- end-datepicker --}}

            <div>
                <x-jet-label value="{{ __('Description') }}" />
                <select class="sl-select" wire:model.defer="event.description" name="event.description" required>
                    {{-- TODO Integrate causes as new model --}}
                    <option value="{{ __('Workday') }}" selected="selected">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='event.description' />
            </div>
        </x-slot>

        <x-slot name='footer'>
            <x-jet-secondary-button wire:click="$set('showModalGetTimeRegisters', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button wire:click="update" wire:loading.attr="disabled" class="ml-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-500 justify-center"
                wire_target="update">
                {{ __('Update event') }}
            </x-jet-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
