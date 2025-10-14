<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Work Center') }} for {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <form action="{{ route('teams.work_centers.store', $team) }}" method="POST">
                        @csrf
                        <div>
                            <x-jet-label for="name" value="{{ __('Name') }}" />
                            <x-jet-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="code" value="{{ __('Code') }}" />
                            <x-jet-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="address" value="{{ __('Address') }}" />
                            <x-jet-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="city" value="{{ __('City') }}" />
                            <x-jet-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="postal_code" value="{{ __('Postal Code') }}" />
                            <x-jet-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code')" />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="state" value="{{ __('State') }}" />
                            <x-jet-input id="state" class="block mt-1 w-full" type="text" name="state" :value="old('state')" />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="country" value="{{ __('Country') }}" />
                            <x-jet-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country')" x-init="$el.value = new Intl.DisplayNames([navigator.language], { type: 'region' }).of(navigator.language.split('-')[1])" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-jet-button>
                                {{ __('Create') }}
                            </x-jet-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
