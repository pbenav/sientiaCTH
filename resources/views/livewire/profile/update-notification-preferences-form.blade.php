<x-jet-form-section submit="updateNotificationPreferences">
    <x-slot name="title">
        {{ __('Notification Preferences') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage your notification preferences.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <label for="notify_by_email" class="flex items-center">
                <x-jet-checkbox id="notify_by_email" wire:model.defer="state.notify_by_email" />
                <span class="ml-2 text-sm text-gray-600">{{ __('Receive email notifications') }}</span>
            </label>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>
