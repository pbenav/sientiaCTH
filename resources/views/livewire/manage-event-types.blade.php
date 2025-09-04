<x-jet-action-section>
    <x-slot name="title">
        {{ __('Event Types') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage the event types for this team.') }}
    </x-slot>

    <x-slot name="content">
        <div class="space-y-6">
            <div class="flex items-center justify-end">
                <x-jet-button wire:click="create">
                    {{ __('Add New Event Type') }}
                </x-jet-button>
            </div>

            <!-- Event Type List -->
            <div class="space-y-2">
                @forelse($eventTypes as $eventType)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $eventType->color }};"></div>
                            {{ $eventType->name }}
                        </div>
                        <div class="flex items-center">
                            <button class="cursor-pointer ml-6 text-sm text-gray-500" wire:click="edit({{ $eventType->id }})">
                                {{ __('Edit') }}
                            </button>
                            <button class="cursor-pointer ml-6 text-sm text-red-500" wire:click="confirmDelete({{ $eventType->id }})">
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <p>{{ __('No event types have been added yet.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Create Event Type Modal -->
        <x-jet-dialog-modal wire:model="showCreateModal">
            <x-slot name="title">{{ __('Add New Event Type') }}</x-slot>
            <x-slot name="content">
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="name" value="{{ __('Event Type Name') }}" />
                    <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="newEventType.name" />
                    <x-jet-input-error for="newEventType.name" class="mt-2" />
                </div>
                <div class="col-span-6 sm:col-span-4 mt-4">
                    <x-jet-label for="color" value="{{ __('Color') }}" />
                    <x-jet-input id="color" type="color" class="mt-1 block" wire:model.defer="newEventType.color" />
                    <x-jet-input-error for="newEventType.color" class="mt-2" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('showCreateModal', false)" wire:loading.attr="disabled">{{ __('Cancel') }}</x-jet-secondary-button>
                <x-jet-button class="ml-2" wire:click="store" wire:loading.attr="disabled">{{ __('Save') }}</x-jet-button>
            </x-slot>
        </x-jet-dialog-modal>

        <!-- Edit Event Type Modal -->
        @if($editingEventType)
            <x-jet-dialog-modal wire:model="showEditModal">
                <x-slot name="title">{{ __('Edit Event Type') }}</x-slot>
                <x-slot name="content">
                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="editing_name" value="{{ __('Event Type Name') }}" />
                        <x-jet-input id="editing_name" type="text" class="mt-1 block w-full" wire:model.defer="editingEventType.name" />
                        <x-jet-input-error for="editingEventType.name" class="mt-2" />
                    </div>
                    <div class="col-span-6 sm:col-span-4 mt-4">
                        <x-jet-label for="editing_color" value="{{ __('Color') }}" />
                        <x-jet-input id="editing_color" type="color" class="mt-1 block" wire:model.defer="editingEventType.color" />
                        <x-jet-input-error for="editingEventType.color" class="mt-2" />
                    </div>
                </x-slot>
                <x-slot name="footer">
                    <x-jet-secondary-button wire:click="$set('showEditModal', false)" wire:loading.attr="disabled">{{ __('Cancel') }}</x-jet-secondary-button>
                    <x-jet-button class="ml-2" wire:click="update" wire:loading.attr="disabled">{{ __('Save') }}</x-jet-button>
                </x-slot>
            </x-jet-dialog-modal>
        @endif

        <!-- Delete Event Type Confirmation Modal -->
        <x-jet-confirmation-modal wire:model="showDeleteModal">
            <x-slot name="title">{{ __('Delete Event Type') }}</x-slot>
            <x-slot name="content">
                {{ __('Are you sure you want to delete this event type? This action cannot be undone.') }}
            </x-slot>
            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled">{{ __('Cancel') }}</x-jet-secondary-button>
                <x-jet-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">{{ __('Delete') }}</x-jet-danger-button>
            </x-slot>
        </x-jet-confirmation-modal>
    </x-slot>
</x-jet-action-section>
