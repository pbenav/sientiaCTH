<div>
    <a class="btn btn-red" wire:click="$set('open', true)">
        <i class="fas fa-trash"></i>
    </a>

    <x-jet-confirmation-modal wire:model="open">
        <x-slot name="title">
            Delete Event
        </x-slot>
    
        <x-slot name="content">
            Are you sure you want to delete this event? This is a permanent deletion.
        </x-slot>
    
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('open')" wire:loading.attr="disabled">
                Cancel
            </x-jet-secondary-button>
    
            <x-jet-danger-button class="ml-2" wire:click="deleteEvent" wire:loading.attr="disabled">
                Delete
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>
    
</div>
