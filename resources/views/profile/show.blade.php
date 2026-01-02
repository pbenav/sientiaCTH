<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div x-data="{ 
        tab: new URLSearchParams(window.location.search).get('tab') || 'account',
        setTab(newTab) {
            this.tab = newTab;
            // Update URL to preserve tab state
            const url = new URL(window.location);
            url.searchParams.set('tab', newTab);
            window.history.replaceState({}, '', url);
        },
        init() {
            // Check for work schedule navigation (only if hash is present)
            if (window.location.hash === '#work-schedule-section') {
                this.tab = 'preferences'; // Ensure preferences tab is active
                
                // Try multiple times to find the element (wait for Livewire to load)
                const attemptScroll = (attempts = 0) => {
                    const scheduleSection = document.getElementById('work-schedule-section');
                    if (scheduleSection) {
                        scheduleSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // Add subtle highlight effect
                        scheduleSection.style.backgroundColor = '#f0f9ff';
                        scheduleSection.style.transition = 'background-color 0.3s ease';
                        setTimeout(() => {
                            scheduleSection.style.backgroundColor = '';
                        }, 2500);
                    } else if (attempts < 10) {
                        // Try again after 500ms, up to 10 times (5 seconds total)
                        setTimeout(() => attemptScroll(attempts + 1), 500);
                    }
                };
                
                // Start attempting after DOM is ready
                this.$nextTick(() => {
                    setTimeout(() => attemptScroll(), 100);
                });
            }
        }
    }">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- Tab Headers -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'account', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'account' }"
                           @click.prevent="setTab('account')">
                            {{ __('Información de cuenta') }}
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'preferences', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'preferences' }"
                           @click.prevent="setTab('preferences')">
                            {{ __('Preferencias') }}
                        </a>
                    </li>
                    <!-- Permissions updated at {{ now() }} -->
                    @can('viewSecurityPanel')
                    <li class="mr-2">
                        <a href="#" class="inline-block p-4 border-b-2 rounded-t-lg"
                           :class="{ 'border-indigo-500 text-indigo-600': tab === 'security', 'border-transparent hover:text-gray-600 hover:border-gray-300': tab !== 'security' }"
                           @click.prevent="setTab('security')">
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
                        @livewire('profile.update-calendar-preferences-form')
                    </div>

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('update-default-work-center-form')
                    </div>

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.user-work-schedule-form', ['user' => Auth::user()])
                    </div>

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.update-notification-preferences-form')
                    </div>

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.update-geolocation-preferences-form')
                    </div>

                    <x-jet-section-border />

                    <div class="mt-10 sm:mt-0">
                        @livewire('profile.update-vacation-preferences-form')
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
    
    <script>
        // Additional script to handle navigation after Livewire loads
        document.addEventListener('DOMContentLoaded', function() {
            // Preserve the current tab when Livewire updates
            window.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', (message, component) => {
                    // Get current tab from Alpine
                    const currentTab = new URLSearchParams(window.location.search).get('tab') || 'account';
                    
                    // Update URL without reloading to preserve tab state
                    if (currentTab && !window.location.search.includes('tab=' + currentTab)) {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', currentTab);
                        window.history.replaceState({}, '', url);
                    }
                });
            });
            
            // Handle navigation to work schedule section
            function scrollToWorkSchedule() {
                const hash = window.location.hash;
                
                if (hash === '#work-schedule-section') {
                    // Wait for Livewire components to load
                    const checkAndScroll = () => {
                        const scheduleSection = document.getElementById('work-schedule-section');
                        if (scheduleSection) {
                            setTimeout(() => {
                                scheduleSection.scrollIntoView({ 
                                    behavior: 'smooth', 
                                    block: 'center' 
                                });
                                
                                // Subtle highlight effect
                                scheduleSection.style.backgroundColor = '#f0f9ff';
                                scheduleSection.style.transition = 'background-color 0.3s ease';
                                
                                setTimeout(() => {
                                    scheduleSection.style.backgroundColor = '';
                                }, 2500);
                            }, 300);
                        } else {
                            // Try again after 500ms
                            setTimeout(checkAndScroll, 500);
                        }
                    };
                    
                    checkAndScroll();
                }
            }
            
            // Run immediately
            scrollToWorkSchedule();
            
            // Also run after Livewire updates
            document.addEventListener('livewire:load', scrollToWorkSchedule);
            document.addEventListener('livewire:update', scrollToWorkSchedule);
        });
    </script>
</x-app-layout>

