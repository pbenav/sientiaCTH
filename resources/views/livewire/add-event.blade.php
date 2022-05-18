<div>
    <x-jet-danger-button wire:click="$set('open', 'true')">
        Add event
    </x-jet-danger-button>

    <x-jet-dialog-modal wire:model="open">

        <x-slot name="title">
            Add new event
        </x-slot>

        <x-slot name="content">

            <div class="mb-4 flex">
                <x-jet-label value="Start Date" class="mt-3 mr-2" wire:init='init'/>
                <x-jet-input type="date" disabled class="mr-2" wire:model.defer='startDate'/>
                <x-jet-input type="time" disabled class="" wire:model.defer='startTime'/>

                <x-jet-input-error for='startTime' />
            </div>

            <div class="mb-4">
                <x-jet-label value="End Date" />
                <x-jet-input type="date" class="w-full"  wire:model.defer="endTime" />

                <x-jet-input-error for='endTime' />
            </div>

            <div class="mb-4">
                <x-jet-label value="Description" />
                <textarea rows="4" class="custom-textarea w-full"  wire:model.defer="description"></textarea>
            </div>

            <div class="mb-4">
                <input type="hidden" id="userId" name="UserId" wire:model.defer="userId">
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('open', false)">
                Cancel
            </x-jet-secondary-button>

            <x-jet-danger-button wire:click="save">
                Create Event
            </x-jet-danger-button>
        </x-slot>

    </x-jet-dialog-modal>

</div>
