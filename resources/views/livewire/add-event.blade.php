<div>
    <x-jet-danger-button wire:click="$set('open', 'true')">
        {{ __('Add event') }}
    </x-jet-danger-button>

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
                <textarea rows="4" class="custom-textarea w-full" placeholder="{{ __('Add a description') }}"
                    wire:model.defer="description"></textarea>
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


            <!-- wire:loading.class="bg-blue-500"
                 wire:loading.remove
                 wire:loading.attr="disabled" class="disabled:bg-blue-500" -->
            <x-jet-danger-button wire:click="save" wire:loading.attr="disabled" class="disabled:bg-blue-500"
                wire_target="save">
                {{ __('Create Event') }}
            </x-jet-danger-button>

            {{-- <span wire:loading.flex wire_target="save">
                Cargando...
            </span> --}}
        </x-slot>

    </x-jet-dialog-modal>

</div>
