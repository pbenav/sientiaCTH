<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-auto">
            <div class="flex min-w-0 shrink">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('events') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                    <!-- App title for desktop/mobile -->
                    <span class="lg:hidden ml-2 font-bold text-gray-800 leading-tight" style="font-size: clamp(0.9rem, 4.5vw, 1.15rem); line-height: 1.1;">
                        sientiaCTH<br><span class="font-normal text-gray-500" style="font-size: clamp(0.7rem, 3vw, 0.9rem);">Control Horario</span>
                    </span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden lg:flex items-center ml-2 flex-nowrap gap-x-1 xl:gap-x-2">
                    <x-nav-link href="{{ route('inicio') }}" :active="request()->routeIs('inicio')">
                        {{ __('Start') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('calendar') }}" :active="request()->routeIs('calendar')">
                        {{ __('Calendar') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('events') }}" :active="request()->routeIs('events')">
                        {{ __('Events') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('stats') }}" :active="request()->routeIs('stats')">
                        {{ __('Stats') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('reports') }}" :active="request()->routeIs('reports')">
                        {{ __('Reports') }}
                    </x-nav-link>
                    @if (Auth::user()->is_admin || Auth::user()->isTeamAdmin() || Auth::user()->isInspector())
                        <x-nav-link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.index')">
                            {{ __('Audit') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link href="{{ route('docs.index') }}" :active="request()->routeIs('docs.*')">
                        {{ __('Documentation') }}
                    </x-nav-link>
                    @can('update', Auth::user()->currentTeam)
                        <x-nav-link href="{{ route('announcements') }}" :active="request()->routeIs('announcements')">
                            {{ __('Announcements') }}
                        </x-nav-link>
                    @endcan
                    @if (Auth::user()->is_admin || Auth::user()->isAnyTeamAdmin())
                        <x-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                            {{ Auth::user()->is_admin ? __('Global Dashboard') : __('Administración') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
                            {{ __('Gestión de Usuarios') }}
                        </x-nav-link>
                    @endif
                    @if (Auth::user()->is_admin)
                        <x-nav-link href="{{ route('admin.teams.index') }}" :active="request()->routeIs('admin.teams.*')">
                            {{ __('Team Administration') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('admin.settings') }}" :active="request()->routeIs('admin.settings')">
                            {{ __('Global Settings') }}
                        </x-nav-link>
                    @endif
                    {{-- Enlace eliminado: Servicio técnico --}}
                </div>
            </div>

            <div class="hidden lg:flex lg:items-center lg:ml-2 xl:ml-3">
                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-tight font-medium rounded-md text-gray-400 bg-white hover:text-gray-700 focus:outline-none transition whitespace-nowrap">
                                        <span class="max-w-[10rem] truncate">{{ Auth::user()->currentTeam?->name ?? __('No Team') }}</span>
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    @if (Auth::user()->currentTeam)
                                        <x-dropdown-link
                                            href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                            {{ __('Team Settings') }}
                                        </x-dropdown-link>
                                    @endif

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    <div class="border-t border-gray-100"></div>

                                    <!-- Team Switcher -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Switch Teams') }}
                                    </div>

                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-switchable-team :team="$team" />
                                    @endforeach
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Notifications -->
                <div class="ml-3 relative flex items-center">
                    @livewire('notification-icon')
                </div>

                <!-- Quick switch to mobile UI -->
                <div class="ml-3 flex items-center">
                    <a href="{{ route('mobile.home') }}" title="{{ __('ui.layout.open_mobile') }}"
                        class="relative inline-flex items-center p-2 rounded-md hover:bg-gray-50 hover:text-gray-700 text-gray-600 transition-colors duration-200"
                        data-tooltip="Abrir versión móvil">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 2a1 1 0 00-1 1v12a1 1 0 001 1h6a1 1 0 001-1V3a1 1 0 00-1-1H7z"></path>
                            <path d="M3 6h14v2H3V6z" fill-opacity="0.6"></path>
                        </svg>
                    </a>
                </div>

                <!-- Language Switcher -->
                <div class="ml-3 flex items-center bg-gray-100 rounded-full px-2 py-1 border border-gray-200">
                    <a href="{{ route('set-locale', 'es') }}"
                        class="px-2 py-1 text-xs font-bold transition-colors {{ app()->getLocale() == 'es' ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600' }}">ES</a>
                    <span class="text-gray-200 text-xs">|</span>
                    <a href="{{ route('set-locale', 'en') }}"
                        class="px-2 py-1 text-xs font-bold transition-colors {{ app()->getLocale() == 'en' ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600' }}">EN</a>
                </div>

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <span class="inline-flex rounded-md">
                                    <span class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-tight font-medium rounded-md text-gray-400 bg-white whitespace-nowrap">
                                        <span class="max-w-[8rem] truncate">{{ Auth::user()->name . ' ' . Auth::user()->family_name1 }}</span></span>
                                    <button
                                        class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                        <img class="h-8 w-8 rounded-full object-cover"
                                            src="{{ Auth::user()->profile_photo_url }}"
                                            alt="{{ Auth::user()->name }} {{ Auth::user()->family_name1 }}" />
                                    </button>
                                </span>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-tight font-medium rounded-md text-gray-400 bg-white hover:text-gray-700 focus:outline-none transition whitespace-nowrap">
                                        <span class="max-w-[8rem] truncate">{{ Auth::user()->name }} {{ Auth::user()->family_name1 }}</span>

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Mobile Notifications and Hamburger -->
            <div class="-mr-2 flex items-center space-x-1 lg:hidden">
                <!-- Mobile Notification Icon -->
                <div class="relative flex items-center justify-center">
                    @livewire('notification-icon')
                </div>

                <!-- Quick switch to mobile UI -->
                <a href="{{ route('mobile.home') }}" title="{{ __('ui.layout.open_mobile') }}"
                    class="relative inline-flex items-center p-1.5 rounded-md hover:bg-gray-50 hover:text-gray-700 text-gray-600 transition-colors duration-200"
                    data-tooltip="Abrir versión móvil">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a1 1 0 00-1 1v12a1 1 0 001 1h6a1 1 0 001-1V3a1 1 0 00-1-1H7z"></path>
                        <path d="M3 6h14v2H3V6z" fill-opacity="0.6"></path>
                    </svg>
                </a>

                <!-- Hamburger Button -->
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-1.5 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Right Side Drawer) -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] lg:hidden" 
         @click="open = false"
         style="display: none;">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-40 backdrop-blur-sm"></div>
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl z-[70] lg:hidden overflow-y-auto"
         style="display: none;">
        
        <div class="flex flex-col h-full border-l border-gray-100">
            <!-- Drawer Header -->
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 pt-8 sm:pt-6">
                <div class="flex items-center">
                    <x-application-mark class="block h-8 w-auto" />
                    <span class="ml-3 font-bold text-gray-800 tracking-tight text-lg">sientiaCTH</span>
                </div>
                <button @click="open = false" class="p-2 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 focus:outline-none transition-all duration-200">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Drawer Content -->
            <div class="flex-1 py-4 overflow-y-auto">
                <div class="px-4 mb-6">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4 px-4">{{ __('Menu') }}</p>
                    <div class="space-y-1">
                        <x-responsive-nav-link href="{{ route('inicio') }}" :active="request()->routeIs('inicio')">
                            {{ __('Start') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('events') }}" :active="request()->routeIs('events')">
                            {{ __('Events') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('calendar') }}" :active="request()->routeIs('calendar')">
                            {{ __('Calendar') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('stats') }}" :active="request()->routeIs('stats')">
                            {{ __('Stats') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('reports') }}" :active="request()->routeIs('reports')">
                            {{ __('Reports') }}
                        </x-responsive-nav-link>
                        @if (Auth::user()->is_admin || Auth::user()->isTeamAdmin() || Auth::user()->isInspector())
                            <x-responsive-nav-link href="{{ route('audit.index') }}" :active="request()->routeIs('audit.index')">
                                {{ __('Audit Log') }}
                            </x-responsive-nav-link>
                        @endif
                        <x-responsive-nav-link href="{{ route('docs.index') }}" :active="request()->routeIs('docs.*')">
                            {{ __('Documentation') }}
                        </x-responsive-nav-link>
                        @can('update', Auth::user()->currentTeam)
                            <x-responsive-nav-link href="{{ route('announcements') }}" :active="request()->routeIs('announcements')">
                                {{ __('Announcements') }}
                            </x-responsive-nav-link>
                        @endcan
                        @if (Auth::user()->is_admin || Auth::user()->isAnyTeamAdmin())
                            <x-responsive-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                                {{ Auth::user()->is_admin ? __('Global Dashboard') : __('Administración') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
                                {{ __('Gestión de Usuarios') }}
                            </x-responsive-nav-link>
                        @endif
                        @if (Auth::user()->is_admin)
                            <x-responsive-nav-link href="{{ route('admin.teams.index') }}" :active="request()->routeIs('admin.teams.*')">
                                {{ __('Team Administration') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link href="{{ route('admin.settings') }}" :active="request()->routeIs('admin.settings')">
                                {{ __('Global Settings') }}
                            </x-responsive-nav-link>
                        @endif
                    </div>
                </div>

                <!-- Account Management -->
                <div class="pt-6 border-t border-gray-100">
                    <div class="px-8 mb-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">{{ __('Account') }}</p>
                    </div>
                    <div class="space-y-1">
                        <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                            {{ __('Profile') }}
                        </x-responsive-nav-link>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                                {{ __('API Tokens') }}
                            </x-responsive-nav-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>

                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="pt-6 mt-6 border-t border-gray-100 pb-8">
                        <div class="px-8 mb-4">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">{{ __('Team Management') }}</p>
                        </div>
                        <div class="space-y-1">
                            @if (Auth::user()->currentTeam)
                                <!-- Team Settings -->
                                <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                                    {{ __('Team Settings') }}
                                </x-responsive-nav-link>

                                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                    <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                                        {{ __('Create New Team') }}
                                    </x-responsive-nav-link>
                                @endcan

                                <div class="border-t border-gray-100 my-4 mx-8"></div>

                                <!-- Team Switcher -->
                                <div class="px-8 py-2">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Switch Teams') }}</p>
                                </div>

                                @foreach (Auth::user()->allTeams() as $team)
                                    <x-switchable-team :team="$team" component="responsive-nav-link" />
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Drawer Footer -->
            <div class="p-6 border-t border-gray-100 bg-gray-50/80">
                <div class="flex items-center">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <img class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-md"
                             src="{{ Auth::user()->profile_photo_url }}"
                             alt="{{ Auth::user()->name }} {{ Auth::user()->family_name1 }}" />
                    @else
                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold border-2 border-white shadow-md text-lg">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif

                    <div class="ml-4 overflow-hidden">
                        <div class="font-bold text-gray-900 truncate tracking-tight">{{ Auth::user()->name }} {{ Auth::user()->family_name1 }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
