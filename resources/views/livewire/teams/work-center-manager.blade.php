<div>
    <div class="space-y-6">
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
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $workCenter->name }}</h3>
                                    <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">{{ $workCenter->code }}</span>
                                </div>
                                
                                @if ($workCenter->address)
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $workCenter->address }}
                                        @if ($workCenter->city), {{ $workCenter->city }}@endif
                                        @if ($workCenter->postal_code) {{ $workCenter->postal_code }}@endif
                                    </p>
                                @endif

                                <!-- NFC Status -->
                                <div class="mt-3">
                                    @if ($workCenter->hasNFC())
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-green-700">{{ __('NFC Enabled') }}</span>
                                                </div>
                                                @if (auth()->user()->ownsTeam($team) || auth()->user()->hasTeamRole($team, 'admin'))
                                                    <button 
                                                        wire:click="regenerateNFCTag({{ $workCenter->id }})"
                                                        class="px-2 py-1 text-xs text-gray-700 bg-white hover:bg-gray-50 rounded border border-gray-300 focus:outline-none"
                                                        title="{{ __('Regenerate NFC') }}">
                                                        {{ __('Regenerate') }}
                                                    </button>
                                                @endif
                                            </div>
                                            
                                            <!-- NFC ID -->
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">{{ __('NFC ID:') }}</span>
                                                <code class="px-2 py-1 text-xs bg-gray-100 rounded font-mono">{{ $workCenter->nfc_tag_id }}</code>
                                                <button 
                                                    wire:click.prevent="copyNFCTagId({{ $workCenter->id }})"
                                                    class="p-1 text-gray-400 hover:text-gray-600 focus:outline-none"
                                                    title="{{ __('Copy NFC ID') }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <!-- NFC Payload Completo -->
                                            @if($workCenter->nfc_payload)
                                                <div class="bg-blue-50 border border-blue-200 rounded p-2">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-xs font-medium text-blue-800">{{ __('Complete NFC Payload (for programming tag):') }}</span>
                                                        <button 
                                                            onclick="copyToClipboard('{{ $workCenter->nfc_payload }}')"
                                                            class="px-2 py-1 text-xs text-gray-700 bg-white hover:bg-gray-50 rounded border border-gray-300 focus:outline-none"
                                                            title="{{ __('Copy Full Payload') }}">
                                                            {{ __('Copy') }}
                                                        </button>
                                                    </div>
                                                    <code class="block text-xs bg-white p-2 rounded border font-mono text-wrap break-all">{{ $workCenter->nfc_payload }}</code>
                                                    <p class="text-xs text-blue-600 mt-1">
                                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        {{ __('This payload includes server URL for automatic Flutter app configuration') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                        @if ($workCenter->nfc_tag_description)
                                            <p class="mt-1 text-xs text-gray-500">{{ $workCenter->nfc_tag_description }}</p>
                                        @endif
                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ __('Generated') }}: {{ $workCenter->nfc_tag_generated_at ? $workCenter->nfc_tag_generated_at->format('d/m/Y H:i') : __('Unknown') }}
                                        </p>
                                    @else
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-gray-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-sm text-gray-500">{{ __('NFC Disabled') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 ml-4">
                                @if (auth()->user()->ownsTeam($team) || auth()->user()->hasTeamRole($team, 'admin'))
                                    <button class="px-3 py-1 text-sm text-indigo-600 hover:text-indigo-900 focus:outline-none" wire:click="confirmWorkCenterUpdate({{ $workCenter->id }})">
                                        {{ __('Edit') }}
                                    </button>

                                    <button class="px-3 py-1 text-sm text-red-500 hover:text-red-700 focus:outline-none" wire:click="confirmWorkCenterRemoval({{ $workCenter->id }})">
                                        {{ __('Remove') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $workCenters->links() }}
            </div>
            @if (auth()->user()->ownsTeam($team) || auth()->user()->hasTeamRole($team, 'admin'))
                <div class="mt-6 flex items-center justify-end">
                    <x-jet-button wire:click="confirmWorkCenterCreation" class="bg-indigo-600 hover:bg-indigo-700">
                        {{ __('Create Work Center') }}
                    </x-jet-button>
                </div>
            @endif
        </x-slot>
    </x-jet-action-section>

    <!-- Unified Work Center Management Modal -->
    <x-jet-dialog-modal wire:model="confirmingWorkCenterManagement">
        <x-slot name="title">
            {{ $workCenterBeingUpdatedId ? __('Edit Work Center') : __('Create Work Center') }}
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
                    <p class="text-xs text-blue-700 mb-4">{{ __('Enable NFC tag for mobile clock-in verification at this work center. The NFC ID will be automatically generated.') }}</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input id="enable_nfc" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" wire:model.defer="state.enable_nfc">
                            <label for="enable_nfc" class="ml-2 block text-sm text-gray-900">
                                {{ __('Enable NFC verification for this work center') }}
                            </label>
                        </div>
                        
                        <div wire:show="state.enable_nfc">
                            <x-jet-label for="nfc_tag_description" value="{{ __('NFC Tag Description') }}" />
                            <textarea id="nfc_tag_description" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" 
                                     wire:model.defer="state.nfc_tag_description" rows="2"
                                     placeholder="{{ __('e.g., Blue NFC sticker on main entrance door') }}"></textarea>
                            <x-jet-input-error for="nfc_tag_description" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Physical description to help locate the NFC tag (optional)') }}</p>
                        </div>
                        
                        <div class="bg-amber-50 border border-amber-200 rounded p-3">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-amber-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-xs text-amber-700">
                                    <p class="font-medium">{{ __('How NFC verification works:') }}</p>
                                    <ul class="mt-1 list-disc list-inside space-y-1">
                                        <li>{{ __('A unique NFC ID will be automatically generated when enabled') }}</li>
                                        <li>{{ __('Program a physical NFC tag with this ID') }}</li>
                                        <li>{{ __('Employees must scan the NFC tag to clock in at this location') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NFC Tag Generation Section (Unified) -->
            <div class="col-span-6 sm:col-span-4 mt-6" wire:show="state.enable_nfc">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-green-900">
                            {{ $workCenterBeingUpdatedId ? __('NFC Tag Content Generation') : __('NFC Tag Content Preview') }}
                        </h4>
                        <button 
                            type="button"
                            wire:click="{{ $workCenterBeingUpdatedId ? 'regenerateNFCContentInModal' : 'generateNFCContentForNew' }}"
                            class="px-2 py-1 text-xs text-gray-700 bg-white hover:bg-gray-50 rounded border border-gray-300 focus:outline-none">
                            {{ $workCenterBeingUpdatedId ? __('Regenerate') : __('Preview NFC Tag') }}
                        </button>
                    </div>
                    <p class="text-xs text-green-700 mb-4">
                        {{ $workCenterBeingUpdatedId 
                            ? __('Content to program into the physical NFC tag (automatically generated, max 128 bytes)') 
                            : __('Preview the content that will be programmed into the NFC tag (max 128 bytes)') 
                        }}
                    </p>
                    
                    @if(!empty($currentNFCContent))
                        <div class="space-y-3">
                            <!-- Size Check -->
                            <div class="flex items-center justify-between p-2 rounded {{ ($currentNFCContent['size_bytes'] ?? 0) <= 128 ? 'bg-white border border-green-300' : 'bg-red-50 border border-red-300' }}">
                                <span class="text-xs font-medium">{{ __('Tag Size') }}:</span>
                                <span class="text-xs {{ ($currentNFCContent['size_bytes'] ?? 0) <= 128 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $currentNFCContent['size_bytes'] ?? 0 }} bytes 
                                    @if(($currentNFCContent['size_bytes'] ?? 0) <= 128)
                                        <svg class="w-3 h-3 inline ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3 inline ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </span>
                            </div>

                            <!-- NFC Payload -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-green-800">{{ $workCenterBeingUpdatedId ? __('Complete NFC Payload') : __('NFC Payload Preview') }}:</label>
                                    <button 
                                        type="button"
                                        onclick="copyToClipboard('{{ $currentNFCContent['payload'] ?? '' }}')"
                                        class="px-2 py-1 text-xs text-gray-700 bg-white hover:bg-gray-50 rounded border border-gray-300 focus:outline-none">
                                        {{ __('Copy Payload') }}
                                    </button>
                                </div>
                                <code class="block text-xs bg-white p-2 rounded border border-green-300 font-mono break-all">{{ $currentNFCContent['payload'] ?? '' }}</code>
                            </div>

                            <!-- Status indicator for preview -->
                            @if(!$workCenterBeingUpdatedId && !($currentNFCContent['is_existing'] ?? false))
                                <div class="bg-amber-50 border border-amber-300 rounded-lg p-2">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-amber-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-xs text-amber-800">{{ __('This is just a preview - Create the work center first, then enable NFC') }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Instructions -->
                            <div class="bg-white border border-green-300 rounded-lg p-2">
                                <div class="text-xs text-green-800">
                                    <p class="font-medium mb-1">{{ __('How to program the NFC tag:') }}</p>
                                    <ol class="list-decimal list-inside space-y-1 text-xs">
                                        <li>{{ __('Copy the complete payload above') }}</li>
                                        <li>{{ __('Use an NFC writing app (NFC Tools, TagWriter, etc.)') }}</li>
                                        <li>{{ __('Create a "Text" record with the copied payload') }}</li>
                                        <li>{{ __('Write to your physical NFC tag') }}</li>
                                        <li>{{ __('Test with the Flutter app') }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endif
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
            <x-jet-secondary-button wire:click="$toggle('confirmingWorkCenterManagement')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2 bg-indigo-600 hover:bg-indigo-700" wire:click="{{ $workCenterBeingUpdatedId ? 'updateWorkCenter' : 'createWorkCenter' }}" wire:loading.attr="disabled">
                {{ $workCenterBeingUpdatedId ? __('Actualizar') : __('Crear') }}
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


    <script>
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyNotification('Copied to clipboard!');
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                fallbackCopyTextToClipboard(text);
            });
        } else {
            fallbackCopyTextToClipboard(text);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showCopyNotification('Copied to clipboard!');
            } else {
                showCopyNotification('Failed to copy', 'error');
            }
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            showCopyNotification('Failed to copy', 'error');
        }
        document.body.removeChild(textArea);
    }

    function showCopyNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg text-white text-sm font-medium z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    // SweetAlert toast notification for saved event
    document.addEventListener('livewire:load', function () {
        Livewire.on('saved', function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ __("Cambios guardados correctamente") }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    });
    </script>
    </div>
    </div>
</div>
