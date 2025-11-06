<div class="mt-10 sm:mt-0">
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Holidays') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Manage your team\'s holidays.') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-6">
                @foreach ($holidays as $holiday)
                    <div class="flex items-center justify-between">
                        <div>
                            {{ $holiday->name }} ({{ $holiday->date->format('d/m/Y') }}) {{ $holiday->type }}
                        </div>

                        @if($isTeamAdmin)
                            <div class="flex items-center">
                                <button class="cursor-pointer ml-6 text-sm text-gray-500"
                                    wire:click="editHoliday({{ $holiday->id }})">
                                    {{ __('Edit') }}
                                </button>

                                <button class="cursor-pointer ml-6 text-sm text-red-500"
                                    wire:click="confirmHolidayDeletion({{ $holiday->id }})">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($isTeamAdmin)
                <div class="mt-4 flex gap-2">
                    <x-jet-button wire:click="$set('managingHoliday', true)">
                        {{ __('Add Holiday') }}
                    </x-jet-button>
                    
                    <x-jet-secondary-button wire:click="openImportHolidays">
                        <i class="fas fa-download mr-2"></i>
                        {{ __('Import Holidays') }}
                    </x-jet-secondary-button>
                </div>
            @endif
        </x-slot>
    </x-jet-action-section>

    <!-- Add Holiday Modal -->
    <x-jet-dialog-modal wire:model="managingHoliday">
        <x-slot name="title">
            {{ $holidayId ? __('Edit Holiday') : __('Add Holiday') }}
        </x-slot>

        <x-slot name="content">
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input id="name" type="text" class="mt-1 block w-full"
                    wire:model.defer="holidayForm.name" />
                <x-jet-input-error for="holidayForm.name" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="date" value="{{ __('Date') }}" />
                <x-jet-input id="date" type="date" class="mt-1 block w-full"
                    wire:model.defer="holidayForm.date" />
                <x-jet-input-error for="holidayForm.date" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="type" value="{{ __('Type') }}" />
                <select id="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    wire:model.defer="holidayForm.type">
                    <option value="">{{ __('Select type...') }}</option>
                    <option value="Nacional">{{ __('Nacional') }}</option>
                    <option value="Regional">{{ __('Regional') }}</option>
                    <option value="Local">{{ __('Local') }}</option>
                    <option value="Otros">{{ __('Otros') }}</option>
                </select>
                <x-jet-input-error for="holidayForm.type" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="managingHoliday(false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="saveHoliday" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    <!-- Delete Holiday Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingHolidayDeletion">
        <x-slot name="title">
            {{ __('Delete Holiday') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to delete this holiday?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingHolidayDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="deleteHoliday" wire:loading.attr="disabled">
                {{ __('Delete') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>

    <!-- Import Holidays Modal -->
    <x-jet-dialog-modal wire:model="importingHolidays" max-width="2xl">
        <x-slot name="title">
            {{ __('Import Holidays') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <div>
                        <x-jet-label for="importYear" value="{{ __('Year') }}" />
                        <select id="importYear" class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            wire:model="importYear">
                            @for($year = now()->year - 1; $year <= now()->year + 2; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    @if($this->getMunicipalityFromWorkCenters())
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{ __('Municipality') }}: <strong>{{ $this->getMunicipalityFromWorkCenters() }}</strong>
                        </div>
                    @endif
                </div>

                @if(count($availableHolidays) > 0)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ __('Available Holidays for :year', ['year' => $importYear]) }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('Select the holidays you want to import') }}
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                            wire:click="toggleSelectAll"
                                            {{ $this->isAllSelected ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('Select All') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="max-h-64 overflow-y-auto">
                            @foreach($availableHolidays as $index => $holiday)
                                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                            wire:model="selectedHolidays" 
                                            value="{{ $index }}"
                                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $holiday['name'] }}
                                                </span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ \Carbon\Carbon::parse($holiday['date'])->format('d/m/Y') }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                        {{ $holiday['type'] === 'Nacional' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                           ($holiday['type'] === 'Regional' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                                        {{ $holiday['type'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar-times text-3xl mb-2"></i>
                        <p>{{ __('No holidays available for import or all holidays for this year are already added.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('importingHolidays', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            @if(count($availableHolidays) > 0)
                <div class="flex gap-2 ml-2">
                    <x-jet-secondary-button wire:click="importAllHolidays" wire:loading.attr="disabled">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        {{ __('Import All') }} ({{ count($availableHolidays) }})
                    </x-jet-secondary-button>
                    
                    <x-jet-button wire:click="importSelectedHolidays" wire:loading.attr="disabled" 
                                  class="{{ count($selectedHolidays) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <i class="fas fa-download mr-2"></i>
                        {{ __('Import Selected') }} ({{ count($selectedHolidays) }})
                    </x-jet-button>
                </div>
            @endif
        </x-slot>
    </x-jet-dialog-modal>
</div>
