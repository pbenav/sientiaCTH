@props(['isteamadmin', 'isinspector', 'eventTypes'])
<div>
    <x-jet-dialog-modal wire:model="showFiltersModal">

        <x-slot name="title">
            {{ __('Set filters to get time register') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-4">
                <x-jet-label value="{{ __('Start date') }}" class="mt-3 mr-2 required" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='filter.start' />
                <x-jet-input-error for='filter.start' />
                
                <x-jet-label value="{{ __('End date') }}" class="mt-3 mr-2 required" />
                <x-jet-input type="date" class="mr-2" wire:model.defer='filter.end' />
                <x-jet-input-error for='filter.end' />
            </div>
            
            <div class="mb-4 flex flex-row flex-wrap gap-2">                
                @if ($isteamadmin || $isinspector)
                    <div class="mb-4">
                        <x-jet-label value="{{ __('Name') }}" />
                        <x-jet-input type="text" wire:model.defer='filter.name' />
                        <x-jet-input-error for='filter.name' />
                    </div>
                    <div class="mb-4">
                        <x-jet-label value="{{ __('Family Name 1') }}" />
                        <x-jet-input type="text" wire:model.defer='filter.family_name1' />
                        <x-jet-input-error for='filter.family_name1' />
                    </div>
                @endif
                <div class="mb-4 text-left sm:text-center">
                    <x-jet-label class="whitespace-nowrap" value="{{ __('Not confirmed') }}" />
                   
                    <x-jet-checkbox class="h-6 w-6 text-gray-600 checked:text-green-600" wire:model.defer='filter.is_open'/>
                    <x-jet-input-error for='filter.is_open' />
                </div>
            </div>

            <div class="mb-4">
                <x-jet-label value="{{ __('Event Type') }}" />
                <select class="sl-select" wire:model.defer='filter.event_type_id'>
                    <option value="">{{ __('All') }}</option>
                    @if(isset($eventTypes))
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                        @endforeach
                    @endif
                </select>
                <x-jet-input-error for='filter.event_type_id' />
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
        </x-slot>
    </x-jet-dialog-modal>
</div>
