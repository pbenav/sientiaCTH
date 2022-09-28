<div>
    <!-- Event detail. Modal table -->
    <x-jet-dialog-modal wire:model="showModalGetTimeRegisters">

        <x-slot name='title'>
            {{ __('Edit event') }}: <span wire:model.defer='event.id'></span>
        </x-slot>

        <x-slot name='content'>

            <div class="mb-4">
                {{-- datepicker --}}
                <x-datepicker label="Start date" wire:model="event.start">
                </x-datepicker>
                <x-jet-input-error for='event.start' />
                <x-datepicker label="End date" wire:model="event.end">
                </x-datepicker>
                <x-jet-input-error for='event.end' />
            </div>
            {{-- end-datepicker --}}

            <div>
                <x-jet-label value="{{ __('Description') }}" />
                <select class="w-full form-control " wire:model.defer="event.description" name="event.description"
                    class="w-full py-2 pl-2 pr-4 mt-2 text-sm border border-gray-400 rounded-lg sm:text-base focus:outline-none focus:border-blue-400"
                    required>
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

            <x-jet-danger-button wire:click="update" wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500"
                wire_target="update">
                {{ __('Update event') }}
            </x-jet-danger-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
