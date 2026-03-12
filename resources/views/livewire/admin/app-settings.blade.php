<div>
    <x-jet-form-section submit="updateSettings">
        <x-slot name="title">
            {{ __('Global Application Settings') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Configure global parameters for the application, such as the background image for login and home pages.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Background Image -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="background" value="{{ __('Current Background Image') }}" />
                <div class="mt-2 flex items-center gap-4">
                    <img src="{{ asset($state['LOGIN_BACKGROUND_IMAGE']) }}" class="h-20 w-32 object-cover rounded shadow border border-gray-200">
                    <span class="text-xs text-gray-500">{{ $state['LOGIN_BACKGROUND_IMAGE'] }}</span>
                </div>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="newBackground" value="{{ __('Upload New Background') }}" />
                <input type="file" id="newBackground" class="mt-1 block w-full" wire:model="newBackground" accept="image/*" />
                <x-jet-input-error for="newBackground" class="mt-2" />
                
                <div wire:loading wire:target="newBackground" class="mt-2 text-sm text-blue-500">
                    {{ __('Uploading...') }}
                </div>
                
                @if ($newBackground)
                    <div class="mt-4">
                        <span class="text-xs text-gray-500">{{ __('Preview') }}:</span>
                        <img src="{{ $newBackground->temporaryUrl() }}" class="h-20 w-32 object-cover rounded shadow mt-1">
                    </div>
                @endif
                
                <p class="mt-2 text-xs text-gray-500">
                    {{ __('Recommended size: 1920x1080px. Format: JPG, PNG. Max: 2MB.') }}
                </p>
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-jet-action-message>

            <x-jet-button wire:loading.attr="disabled" wire:target="newBackground, updateSettings">
                {{ __('Save Settings') }}
            </x-jet-button>
        </x-slot>
    </x-jet-form-section>

    @if (session()->has('message'))
        <div class="mt-4 p-4 rounded-md {{ session('messageType') === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
            {{ session('message') }}
        </div>
    @endif
</div>
