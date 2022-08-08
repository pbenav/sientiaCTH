<div>
   
    <x-jet-dialog-modal wire:model="open">

        <x-slot name="title">
            {{ __('Add new event') }}
        </x-slot>

        <x-slot name="content">

            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='start_date' />                
                <x-jet-input-error for='start_date' />
                <x-jet-input type="time" class="" wire:model.defer='start_time' />
                <x-jet-input-error for='start_time' />
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Description') }}" />
                <select class="custom-textarea w-full" wire:model="description" name="description"
                    class="mt-2 text-sm sm:text-base pl-2 pr-4 rounded-lg border border-gray-400 w-full py-2 focus:outline-none focus:border-blue-400"
                    required>
                    <option value="{{ __('Choose a description') }}">{{ __('Elige una descripción') }}</option>
                    <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                    <option value="{{ __('Lunch') }}">{{ __('Lunch') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>

            <div class="mb-4">
                <input type="hidden" id="user_id" name="user_id" wire:model.defer="user_id">
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('open', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button wire:click="save" wire:loading.attr="disabled" class="disabled:bg-blue-500 ml-2"
                wire_target="save">
                {{ __('Create Event') }}
            </x-jet-danger-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
