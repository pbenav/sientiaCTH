<x-jet-form-section submit="updateCalendarPreferences">
    <x-slot name="title">
        {{ __('Calendar Preferences') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Configure your calendar display preferences.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Week Starts On -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="week_starts_on" value="{{ __('Week starts on') }}" />
            <select id="week_starts_on" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" wire:model.defer="state.week_starts_on">
                <option value="1">{{ __('Monday') }}</option>
                <option value="0">{{ __('Sunday') }}</option>
            </select>
            <x-jet-input-error for="state.week_starts_on" class="mt-2" />
            <p class="mt-2 text-sm text-gray-600">
                {{ __('Choose which day your calendar week should start on.') }}
            </p>
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
