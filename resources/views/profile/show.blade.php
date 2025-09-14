<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div x-data="{ tab: 'account' }">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- Tab Headers -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'account', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'account' }"
                           @click.prevent="tab = 'account'">
                            {{ __('Información de cuenta') }}
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'preferences', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'preferences' }"
                           @click.prevent="tab = 'preferences'">
                            {{ __('Preferencias') }}
                        </a>
                    </li>
                    <!-- Permissions updated at {{ now() }} -->
                    @can('viewSecurityPanel')
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'security', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'security' }"
                           @click.prevent="tab = 'security'">
                            {{ __('Seguridad') }}
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>

            <!-- Tab Content -->
            <div>
                <!-- Account Information Tab -->
                <div x-show="tab === 'account'">
                    @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                        @livewire('profile.update-profile-information-form')
                        <x-jet-section-border />
                    @endif

                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                        <div class="mt-10 sm:mt-0">
                            @livewire('profile.update-password-form')
                        </div>
                        <x-jet-section-border />
                    @endif

                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <div class="mt-10 sm:mt-0">
                            @livewire('profile.two-factor-authentication-form')
                        </div>
                        <x-jet-section-border />
                    @endif

                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.logout-other-browser-sessions-form')
                    </div>

                    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                        <x-jet-section-border />
                        <div class="mt-10 sm:mt-0">
                            @livewire('profile.delete-user-form')
                        </div>
                    @endif
                </div>

                <!-- Preferences Tab -->
                <div x-show="tab === 'preferences'">
                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.user-work-schedule-form', ['user' => Auth::user()])
                    </div>

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.update-notification-preferences-form')
                    </div>
                </div>

                @can('viewSecurityPanel')
                <!-- Security Tab -->
                <div x-show="tab === 'security'">
                    <div class="mt-10 sm:mt-0">
                        @livewire('security.blocked-ip-manager')
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
