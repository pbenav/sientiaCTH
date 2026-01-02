<x-jet-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" class="hidden"
                            wire:model="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-jet-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }} {{ $this->user->family_name1 }}" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-jet-secondary-button class="mt-2 mr-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-jet-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-jet-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-jet-secondary-button>
                @endif

                <x-jet-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('Name') }}" class="required" />
            <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" autocomplete="name"/>
            <x-jet-input-error for="name" class="mt-2" />
        </div>

         <!-- Familyname 1 -->
         <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="family_name1" value="{{ __('Family Name 1') }}" class="required" />
            <x-jet-input id="family_name1" type="text" class="mt-1 block w-full" wire:model.defer="state.family_name1" autocomplete="family_name1"/>
            <x-jet-input-error for="family_name1" class="mt-2" />
        </div>

        <!-- Familyname 2 -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="family_name2" value="{{ __('Family Name 2') }}" />
            <x-jet-input id="family_name2" type="text" class="mt-1 block w-full" wire:model.defer="state.family_name2" autocomplete="family_name2"/>
            <x-jet-input-error for="family_name2" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="email" value="{{ __('Email') }}" class="required" />
            <x-jet-input id="email" type="email" class="mt-1 block w-full" wire:model.defer="state.email"/>
            <x-jet-input-error for="email" class="mt-2" />
        </div>

        <!-- UserCode -->
        <div class="col-span-6 sm:col-span-4" x-data="{ showUserCode: false }">
            <x-jet-label for="user_code" value="{{ __('User Code') }}" class="required" />
            <div class="relative">
                <x-jet-input 
                    id="user_code" 
                    x-bind:type="showUserCode ? 'text' : 'password'" 
                    class="mt-1 block w-full pr-10" 
                    wire:model.defer="state.user_code"
                    autocomplete="off"
                />
                <button 
                    type="button"
                    @click="showUserCode = !showUserCode"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <svg x-show="!showUserCode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showUserCode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.96 9.96 0 012.292-3.938m0 0A9.96 9.96 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-5.6-5.6l-2.8-2.8m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-jet-input-error for="user_code" class="mt-2" />
        </div>

        <!-- Language / Locale -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="locale" value="{{ __('Language') }}" />
            <select id="locale" wire:model.defer="state.locale" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                <option value="es">Español</option>
                <option value="en">English</option>
            </select>
            <x-jet-input-error for="locale" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button wire:loading.attr="disabled" wire:target="photo" class="bg-indigo-600 hover:bg-indigo-700">
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>
