<div>
    <a class="btn btn-blue" wire:click="$set('open', true)">
        <i class="fas fa-edit"></i>
    </a>

    <x-jet-dialog-modal wire:model="open">

        <x-slot name='title'>
            Editar evento {{ $event->description }}
        </x-slot>

        @php
            $now = date("h:i:sa");
        @endphp

        <x-slot name='content'>
            <div class="mb-4">
                <x-jet-label value="Event End" />
                <x-jet-input wire:model="event.endTime" type="text" value="{{ $now }}" class="w-full" />                
            </div>

            <div>
                <x-jet-label value="Event description" />
                <x-jet-input wire:model="event.description" type="text" class="custom-textarea w-full"/>
            </div>
        </x-slot>

        <x-slot name='footer'>
            <x-jet-secondary-button wire:click="$set('open', false)">
                Cancel
            </x-jet-secondary-button>

            <x-jet-danger-button wire:click="save" wire:loading.attr="disabled" class="disabled:opacity-60">
                Update
            </x-jet-danger-button>
        </x-slot>

    </x-jet-dialog-modal>

</div>