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
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <x-jet-button wire:click="$set('managingHoliday', true)">
                    {{ __('Add Holiday') }}
                </x-jet-button>
            </div>
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
                <x-jet-input id="name" type="text" class="mt-1 block w-full"
                    wire:model.defer="holidayForm.type" />
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
</div>
