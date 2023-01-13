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
                @if ($isteamadmin || $isinspector)
                    <div class="mb-4">
                        <x-jet-label value="{{ __('Name') }}" />
                        <x-jet-input type="text" wire:model.defer='filter.name' />
                        <x-jet-input-error for='filter.name' />
                    </div>
                    <div class="mb-4 ml-2">
                        <x-jet-label value="{{ __('Family Name 1') }}" />
                        <x-jet-input type="text" wire:model.defer='filter.family_name1' />
                        <x-jet-input-error for='filter.family_name1' />
                    </div>
                @endif
                <div class="mb-4 ml-2 text-center">
                    <x-jet-label class="w-auto" value="{{ __('Not confirmed') }}" />
                   
                    <x-jet-checkbox class="h-6 w-6 ml-2 text-gray-600 checked:text-green-600" wire:model.defer='filter.is_open'/>
                    <x-jet-input-error for='filter.is_open' />
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
                <x-jet-input-error for='filter.description' />
            </div>
            
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showFiltersModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button wire:click="$set('showFiltersModal', false)"
                wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500">
                {{ __('Set filter') }}
            </x-jet-danger-button>

            <x-jet-button wire:click="unsetFilter" wire:loading.attr="disabled" class="ml-2 disabled:bg-blue-500">
                {{ __('Unset filter') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
