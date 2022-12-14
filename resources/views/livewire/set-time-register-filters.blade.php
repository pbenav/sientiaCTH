<div>
    <x-jet-dialog-modal wire:model="showFiltersModal">

        <x-slot name="title">
            {{ __('Set filters to get time register') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='filt.start' />
                <x-jet-input-error for='filt.start' />
                
                <x-jet-label value="{{ __('End date') }}" class="mt-3 mr-2" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='filt.end' />
                <x-jet-input-error for='filttend' />
            </div>
            
            <div class="mb-4 flex">
                @if (true)
                    <div class="mb-4">
                        <x-jet-label value="{{ __('Name') }}" />
                        <x-jet-input type="text" wire:model.defer='filt.name' />
                        <x-jet-input-error for='filt.name' />
                    </div>
                    <div class="mb-4 ml-2">
                        <x-jet-label value="{{ __('Family Name 1') }}" />
                        <x-jet-input type="text" wire:model.defer='filt.family_name1' />
                        <x-jet-input-error for='filt.family_name1' />
                    </div>
                @endif
                <div class="mb-4 ml-2 text-center">
                    <x-jet-label class="w-auto" value="{{ __('Is confirmed') }}" />
                    <x-jet-checkbox class="mt-2" wire:model.defer='filt.is_open' />
                    <x-jet-input-error for='filt.is_confirmed' />
                </div>
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Description') }}" />
                <select class="sl-select" wire:model.defer='filt.description'>
                    <option value="{{ __('All') }}">{{ __('All') }}</option>
                    <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }} </option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='filt.description' />
            </div>
            
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showFiltersModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button wire:click="$set('filtered', 'true')" wire:click="setFilters"
                wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500">
                {{ __('Set filter') }}
            </x-jet-danger-button>

            <x-jet-button wire:click="unSetFilters" wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500"
                wire:target="unSetFilter">
                {{ __('Unset filter') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
