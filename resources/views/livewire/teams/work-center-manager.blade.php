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
                                <button class="cursor-pointer ml-6 text-sm text-indigo-600 hover:text-indigo-900 focus:outline-none" wire:click="confirmWorkCenterUpdate({{ $workCenter->id }})">
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
            @if (auth()->user()->ownsTeam($team) || auth()->user()->hasTeamRole($team, 'admin'))
                <div class="mt-6 flex items-center justify-end">
                    <x-jet-button wire:click="confirmWorkCenterCreation">
                        {{ __('Create Work Center') }}
                    </x-jet-button>
                </div>
            @endif
        </x-slot>
    </x-jet-action-section>

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

            <!-- NFC Configuration Section -->
            <div class="col-span-6 sm:col-span-4 mt-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-3">{{ __('NFC Configuration') }}</h4>
                    <p class="text-xs text-blue-700 mb-4">{{ __('Configure NFC tag for mobile clock-in verification at this work center') }}</p>
                    
                    <div class="space-y-4">
                        <div>
                            <x-jet-label for="nfc_tag_id" value="{{ __('NFC Tag ID') }}" />
                            <x-jet-input id="nfc_tag_id" type="text" class="mt-1 block w-full" wire:model.defer="state.nfc_tag_id" 
                                        placeholder="{{ __('e.g., 04:A3:22:B2:C4:15:80') }}" />
                            <x-jet-input-error for="nfc_tag_id" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Unique identifier of the NFC tag (obtained from Flutter app)') }}</p>
                        </div>
                        
                        <div>
                            <x-jet-label for="nfc_tag_description" value="{{ __('NFC Tag Description') }}" />
                            <textarea id="nfc_tag_description" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" 
                                     wire:model.defer="state.nfc_tag_description" rows="2"
                                     placeholder="{{ __('e.g., Blue NFC sticker on main entrance door') }}"></textarea>
                            <x-jet-input-error for="nfc_tag_description" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Physical description to help locate the NFC tag') }}</p>
                        </div>
                    </div>
                </div>
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

            <!-- NFC Configuration Section -->
            <div class="col-span-6 sm:col-span-4 mt-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-3">{{ __('NFC Configuration') }}</h4>
                    <p class="text-xs text-blue-700 mb-4">{{ __('Configure NFC tag for mobile clock-in verification at this work center') }}</p>
                    
                    <div class="space-y-4">
                        <div>
                            <x-jet-label for="edit_nfc_tag_id" value="{{ __('NFC Tag ID') }}" />
                            <x-jet-input id="edit_nfc_tag_id" type="text" class="mt-1 block w-full" wire:model.defer="state.nfc_tag_id" 
                                        placeholder="{{ __('e.g., 04:A3:22:B2:C4:15:80') }}" />
                            <x-jet-input-error for="nfc_tag_id" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Unique identifier of the NFC tag (obtained from Flutter app)') }}</p>
                        </div>
                        
                        <div>
                            <x-jet-label for="edit_nfc_tag_description" value="{{ __('NFC Tag Description') }}" />
                            <textarea id="edit_nfc_tag_description" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" 
                                     wire:model.defer="state.nfc_tag_description" rows="2"
                                     placeholder="{{ __('e.g., Blue NFC sticker on main entrance door') }}"></textarea>
                            <x-jet-input-error for="nfc_tag_description" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Physical description to help locate the NFC tag') }}</p>
                        </div>
                    </div>
                </div>
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
