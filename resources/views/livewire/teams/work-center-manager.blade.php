<div>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Work Centers') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Manage the work centers associated with this team.') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-6">
                @foreach ($workCenters as $workCenter)
                    <div class="flex items-center justify-between">
                        <div>
                            {{ $workCenter->name }}
                        </div>

                        <div class="flex items-center">
                            @if (auth()->user()->ownsTeam($team) || auth()->user()->hasTeamRole($team, 'admin'))
                                <button class="cursor-pointer ml-6 text-sm text-gray-400 focus:outline-none" wire:click="confirmWorkCenterUpdate({{ $workCenter->id }})">
                                    {{ __('Edit') }}
                                </button>

                                <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none" wire:click="confirmWorkCenterRemoval({{ $workCenter->id }})">
                                    {{ __('Remove') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $workCenters->links() }}
            </div>
        </x-slot>
    </x-jet-action-section>

    @if (auth()->user()->ownsTeam($team) || auth()->user()->hasTeamRole($team, 'admin'))
        <x-jet-section-border />

        <div class="mt-10 sm:mt-0">
            <x-jet-action-section>
                <x-slot name="title">
                    {{ __('Create Work Center') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Add a new work center to your team.') }}
                </x-slot>

                <x-slot name="content">
                    <x-jet-button wire:click="confirmWorkCenterCreation">
                        {{ __('Create Work Center') }}
                    </x-jet-button>
                </x-slot>
            </x-jet-action-section>
        </div>
    @endif

    <!-- Create Work Center Modal -->
    <x-jet-dialog-modal wire:model="confirmingWorkCenterCreation">
        <x-slot name="title">
            {{ __('Create Work Center') }}
        </x-slot>

        <x-slot name="content">
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" />
                <x-jet-input-error for="name" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="code" value="{{ __('Code') }}" />
                <x-jet-input id="code" type="text" class="mt-1 block w-full" wire:model.defer="state.code" />
                <x-jet-input-error for="code" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="address" value="{{ __('Address') }}" />
                <x-jet-input id="address" type="text" class="mt-1 block w-full" wire:model.defer="state.address" />
                <x-jet-input-error for="address" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="city" value="{{ __('City') }}" />
                <x-jet-input id="city" type="text" class="mt-1 block w-full" wire:model.defer="state.city" />
                <x-jet-input-error for="city" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="postal_code" value="{{ __('Postal Code') }}" />
                <x-jet-input id="postal_code" type="text" class="mt-1 block w-full" wire:model.defer="state.postal_code" />
                <x-jet-input-error for="postal_code" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="state" value="{{ __('State') }}" />
                <x-jet-input id="state" type="text" class="mt-1 block w-full" wire:model.defer="state.state" />
                <x-jet-input-error for="state" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="country" value="{{ __('Country') }}" />
                <x-jet-input id="country" type="text" class="mt-1 block w-full" wire:model.defer="state.country" />
                <x-jet-input-error for="country" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingWorkCenterCreation')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="createWorkCenter" wire:loading.attr="disabled">
                {{ __('Create') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    <!-- Update Work Center Modal -->
    <x-jet-dialog-modal wire:model="confirmingWorkCenterUpdate">
        <x-slot name="title">
            {{ __('Edit Work Center') }}
        </x-slot>

        <x-slot name="content">
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" />
                <x-jet-input-error for="name" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="code" value="{{ __('Code') }}" />
                <x-jet-input id="code" type="text" class="mt-1 block w-full" wire:model.defer="state.code" />
                <x-jet-input-error for="code" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="address" value="{{ __('Address') }}" />
                <x-jet-input id="address" type="text" class="mt-1 block w-full" wire:model.defer="state.address" />
                <x-jet-input-error for="address" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="city" value="{{ __('City') }}" />
                <x-jet-input id="city" type="text" class="mt-1 block w-full" wire:model.defer="state.city" />
                <x-jet-input-error for="city" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="postal_code" value="{{ __('Postal Code') }}" />
                <x-jet-input id="postal_code" type="text" class="mt-1 block w-full" wire:model.defer="state.postal_code" />
                <x-jet-input-error for="postal_code" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="state" value="{{ __('State') }}" />
                <x-jet-input id="state" type="text" class="mt-1 block w-full" wire:model.defer="state.state" />
                <x-jet-input-error for="state" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="country" value="{{ __('Country') }}" />
                <x-jet-input id="country" type="text" class="mt-1 block w-full" wire:model.defer="state.country" />
                <x-jet-input-error for="country" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingWorkCenterUpdate')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="updateWorkCenter" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    <!-- Remove Work Center Confirmation Modal -->
    <x-jet-confirmation-modal wire:model="confirmingWorkCenterRemoval">
        <x-slot name="title">
            {{ __('Remove Work Center') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to remove this work center?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingWorkCenterRemoval')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="removeWorkCenter" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-confirmation-modal>
</div>
