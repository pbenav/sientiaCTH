<div>
    <x-jet-dialog-modal wire:model="showAddEventModal">

        <x-slot name="title">
            {{ __('Add new event') }}
        </x-slot>

        <x-slot name="content">

            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2 required" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='start_date' />
                <x-jet-input-error for='start_date' />
                <x-jet-input type="time" class="" wire:model.defer='start_time' />
                <x-jet-input-error for='start_time' />
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Description') }}" class="required" />
                <select class="w-full form-control " wire:model="description" name="description"
                    class="w-full py-2 pl-2 pr-4 mt-2 text-sm border border-gray-400 rounded-lg sm:text-base focus:outline-none focus:border-blue-400"
                    required>
                    <option value="{{ __('Workday') }}" selected="selected">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>

            <div class="mb-4">
                <input type="hidden" id="user_id" name="user_id" wire:model.defer="user_id">
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showAddEventModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            {{-- Function save('') empty parameter to say that we are already in dashboard --}}
            <x-jet-danger-button wire:click="save('')" wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500"
                wire_target="save">
                {{ __('Create Event') }}
            </x-jet-danger-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
