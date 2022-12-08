<div>
    <x-jet-dialog-modal wire:model="showFiltersModal">

        <x-slot name="title">
            {{ __('Set filters to get time register') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='filter.start' />
                <x-jet-input-error for='filter.start' />

                <x-jet-label value="{{ __('End date') }}" class="mt-3 mr-2" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='filter.end' />
                <x-jet-input-error for='filter.end' />
            </div>

            <div class="mb-4 flex">
                @if (true)
                    <div class="mb-4">
                        <x-jet-label value="{{ __('Name') }}" />
                        <x-jet-input type="text" wire:model.defer='filter.name' />
                        <x-jet-input-error for='name' />
                    </div>
                    <div class="mb-4 ml-2">
                        <x-jet-label value="{{ __('Family name 1') }}" />
                        <x-jet-input type="text" wire:model.defer='filter.family_name1' />
                        <x-jet-input-error for='family_name1' />
                    </div>
                @endif
                <div class="mb-4 ml-2 text-center">
                    <x-jet-label class="w-auto" value="{{ __('Is confirmed') }}" />
                    <x-jet-checkbox class="mt-2" wire:model.defer='filter.is_open' />
                    <x-jet-input-error for='is_confirmed' />
                </div>
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Description') }}" />
                <select class="sl-select" wire:model.defer='filter.description'>
                    <option value="{{ __('All') }}">{{ __('All') }}</option>
                    <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }} </option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showFiltersModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button wire:click="setFilters" wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500"
                wire_target="setFilters">
                {{ __('Set filters') }}
            </x-jet-danger-button>

            <x-jet-button wire:click="unSetFilters" wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500"
            wire_target="unSetFilters">
            {{ __('Unset filters') }}
        </x-jet-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>
