<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Start') }}
            </h2>
            <div class="flex gap-2" x-data>
                <button 
                    @click="$store.dashboard.toggleCustomization()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                    x-text="$store.dashboard.customizationMode ? '{{ __('Done') }}' : '{{ __('Customize Dashboard') }}'"
                >{{ __('Customize Dashboard') }}</button>
                <button 
                    @click="$store.dashboard.resetLayout()"
                    x-show="$store.dashboard.customizationMode"
                    style="display: none;"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
                >
                    {{ __('Reset to Default') }}
                </button>
            </div>
        </div>
    </x-slot>



    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                {{-- Clock-in Widget - Left Column (spans 1 column on large screens) --}}
                @if(Auth::user()->ownsTeam(Auth::user()->currentTeam) || (!Auth::user()->is_admin && !Auth::user()->isInspector()))
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg h-full">
                        <div class="p-6">
                            <div class="mb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ __('Clock In/Out') }}</h3>
                                        <p class="text-sm text-gray-600">{{ __('Manage your work time registration') }}</p>
                                    </div>
                                    @if(Auth::user()->geolocation_enabled)
                                        <div class="flex items-center space-x-1 px-2 py-1 bg-green-50 border border-green-200 rounded-md" title="{{ __('GPS geolocation enabled') }}">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-green-700">GPS</span>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-1 px-2 py-1 bg-gray-50 border border-gray-200 rounded-md" title="{{ __('GPS geolocation disabled') }}">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-gray-500">GPS</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 mt-3">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y') }}
                                </div>
                            </div>
                            @livewire('smart-clock-button')
                        </div>
                    </div>
                </div>
                @endif
                
                {{-- Dashboard Content - Right Column (spans 3 columns on large screens) --}}
                <div class="{{ (!Auth::user()->ownsTeam(Auth::user()->currentTeam) && (Auth::user()->is_admin || Auth::user()->isInspector())) ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                    
                    {{-- Customization Mode Hint --}}
                    <div id="customizationHint" style="display: none;" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-blue-800 font-medium">{{ __('Customization Mode Active') }}</p>
                                <p class="text-sm text-blue-700 mt-1">{{ __('Drag widgets to reorder them. Click the eye icon to hide/show widgets. Changes are saved automatically.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sortable Widgets Container --}}
                    <div id="widgets-container" class="flex flex-wrap gap-4 sm:gap-6">
                        {{-- Widgets will be rendered here based on user preferences --}}
                    </div>

                </div>

            </div>
        </div>
    </div>

    <template id="widget-announcements">
        <div class="bg-white rounded-lg shadow-xl p-6 flex flex-col h-[500px]">
            <div class="flex justify-between items-center mb-4 flex-shrink-0 widget-handle cursor-move">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Team Announcements') }}</h3>
                <div class="flex items-center gap-2" x-show="$store.dashboard.customizationMode">
                    <button onclick="moveWidget('announcements', -1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Previous') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                    </button>
                    <button onclick="moveWidget('announcements', 1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Next') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <button onclick="resizeWidget('announcements')" class="hidden lg:inline-flex text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Resize') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    </button>
                    <button @click="$store.dashboard.toggleWidget('announcements')" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Hide') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto overflow-x-hidden pr-2" style="scrollbar-width: thin; scrollbar-color: #cbd5e0 #f7fafc; min-height: 0;">
                @livewire('team-announcements')
            </div>
        </div>
    </template>

    <template id="widget-inbox-summary">
        <div class="bg-white rounded-lg shadow-xl flex flex-col max-h-[400px]">
            <div class="p-6 pb-4 flex-shrink-0">
                <div class="flex justify-between items-center widget-handle cursor-move">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Inbox') }}</h3>
                    <div class="flex items-center gap-2" x-show="$store.dashboard.customizationMode">
                        <button onclick="moveWidget('inbox-summary', -1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Previous') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </button>
                        <button onclick="moveWidget('inbox-summary', 1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Next') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <button onclick="resizeWidget('inbox-summary')" class="hidden lg:inline-flex text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Resize') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                        </button>
                        <button @click="$store.dashboard.toggleWidget('inbox-summary')" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Hide') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-6 pb-6" style="scrollbar-width: thin; scrollbar-color: #cbd5e0 #f7fafc;">
                @livewire('inbox-summary-component')
            </div>
        </div>
    </template>

    <template id="widget-sent-messages-summary">
        <div class="bg-white rounded-lg shadow-xl flex flex-col max-h-[400px]">
            <div class="p-6 pb-4 flex-shrink-0">
                <div class="flex justify-between items-center widget-handle cursor-move">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Sent Messages') }}</h3>
                    <div class="flex items-center gap-2" x-show="$store.dashboard.customizationMode">
                        <button onclick="moveWidget('sent-messages-summary', -1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Previous') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </button>
                        <button onclick="moveWidget('sent-messages-summary', 1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Next') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <button onclick="resizeWidget('sent-messages-summary')" class="hidden lg:inline-flex text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Resize') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                        </button>
                        <button @click="$store.dashboard.toggleWidget('sent-messages-summary')" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Hide') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-6 pb-6" style="scrollbar-width: thin; scrollbar-color: #cbd5e0 #f7fafc;">
                @livewire('sent-messages-summary-component')
            </div>
        </div>
    </template>

    <template id="widget-stats-cards">
        <div class="md:col-span-2 bg-white rounded-lg shadow-xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 widget-handle cursor-move">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Statistics') }}</h3>
                    <div class="flex items-center gap-2" x-show="$store.dashboard.customizationMode">
                        <button onclick="moveWidget('stats-cards', -1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Previous') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </button>
                        <button onclick="moveWidget('stats-cards', 1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Next') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <button onclick="resizeWidget('stats-cards')" class="hidden lg:inline-flex text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Resize') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                        </button>
                        <button @click="$store.dashboard.toggleWidget('stats-cards')" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Hide') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                @livewire('dashboard-stats-component')
            </div>
        </div>
    </template>

    <template id="widget-latest-clock-ins">
        <div class="bg-white rounded-lg shadow-xl flex flex-col max-h-[400px]">
            <div class="p-6 pb-4 flex-shrink-0">
                <div class="flex justify-between items-center widget-handle cursor-move">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Recent Activity') }}</h3>
                    <div class="flex items-center gap-2" x-show="$store.dashboard.customizationMode">
                        <button onclick="moveWidget('latest-clock-ins', -1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Previous') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                        </button>
                        <button onclick="moveWidget('latest-clock-ins', 1)" class="text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Move Next') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <button onclick="resizeWidget('latest-clock-ins')" class="hidden lg:inline-flex text-gray-400 hover:text-blue-600 transition-colors" title="{{ __('Resize') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4M4 20l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                        </button>
                        <button @click="$store.dashboard.toggleWidget('latest-clock-ins')" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Hide') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: #cbd5e0 #f7fafc;">
                @livewire('latest-clock-ins-widget')
            </div>
        </div>
    </template>

    @push('alpine-stores')
    {{-- Initialize Alpine store before Alpine.js loads --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('dashboard', {
                customizationMode: false,
                sortable: null,
                preferences: @json(Auth::user()->getDashboardWidgetOrder()),
                isDesktop: window.innerWidth >= 1024,
                isTablet: window.innerWidth >= 768 && window.innerWidth < 1024,
                isMobile: window.innerWidth < 768,
                
                init() {
                    // Listen for window resize to update breakpoints
                    window.addEventListener('resize', () => {
                        this.updateBreakpoints();
                    });
                },
                
                updateBreakpoints() {
                    const width = window.innerWidth;
                    this.isDesktop = width >= 1024;
                    this.isTablet = width >= 768 && width < 1024;
                    this.isMobile = width < 768;
                    
                    // Reinitialize sortable with new settings
                    if (this.sortable) {
                        this.initSortable();
                    }
                    
                    // Re-render widgets with new breakpoint
                    this.renderWidgets();
                },
                
                toggleCustomization() {
                    this.customizationMode = !this.customizationMode;
                    if (this.sortable) {
                        this.sortable.option('disabled', !this.customizationMode);
                    }
                    if (this.customizationMode) {
                        document.body.classList.add('customization-active');
                        document.getElementById('customizationHint').style.display = 'block';
                    } else {
                        document.body.classList.remove('customization-active');
                        document.getElementById('customizationHint').style.display = 'none';
                    }
                },
                
                renderWidgets() {
                    const container = document.getElementById('widgets-container');
                    if (!container) return;
                    
                    container.innerHTML = '';
                    
                    this.preferences.order.forEach(widgetId => {
                        if (!this.preferences.hidden.includes(widgetId)) {
                            const template = document.getElementById(`widget-${widgetId}`);
                            if (template) {
                                const clone = template.content.cloneNode(true);
                                const widgetDiv = clone.querySelector('div');
                                widgetDiv.classList.add('widget-item');
                                widgetDiv.setAttribute('data-widget-id', widgetId);
                                
                                // Calculate flex-basis based on saved size
                                let flexBasis = '100%';
                                
                                if (this.isMobile) {
                                    // Mobile: always full width
                                    flexBasis = '100%';
                                } else if (this.isTablet) {
                                    // Tablet: limit to 100% or 50%
                                    const savedSize = this.preferences.sizes?.[widgetId] || 12;
                                    flexBasis = savedSize <= 6 ? 'calc(50% - 0.75rem)' : '100%';
                                } else {
                                    // Desktop: calculate from saved size (or defaults)
                                    let colSpan = 12;
                                    if (!this.preferences.sizes || !this.preferences.sizes[widgetId]) {
                                        if (widgetId === 'inbox-summary' || widgetId === 'sent-messages-summary') {
                                            colSpan = 6;
                                        } else if (widgetId === 'latest-clock-ins') {
                                            colSpan = 6;
                                        }
                                    } else {
                                        colSpan = this.preferences.sizes[widgetId];
                                    }
                                    // Convert 12-column grid to percentage
                                    flexBasis = `calc(${(colSpan / 12) * 100}% - 1.5rem)`;
                                }

                                // Apply flex styling for auto-expansion
                                widgetDiv.style.flexBasis = flexBasis;
                                widgetDiv.style.flexGrow = '1';
                                widgetDiv.style.flexShrink = '1';
                                widgetDiv.style.minWidth = this.isMobile ? '100%' : '300px';
                                
                                container.appendChild(clone);
                            }
                        }
                    });
                    
                    if (window.Livewire) {
                        window.Livewire.rescan();
                    }
                },
                
                resizeWidget(widgetId) {
                    if (!this.preferences.sizes) this.preferences.sizes = {};
                    
                    let currentSize = this.preferences.sizes[widgetId] || 12;
                    let newSize = 12;
                    
                    if (this.isMobile) {
                        // No resizing on mobile
                        return;
                    } else if (this.isTablet) {
                        // Tablet: toggle between 12 (100%) and 6 (50%)
                        newSize = currentSize === 12 ? 6 : 12;
                    } else {
                        // Desktop: cycle through 12 (100%) -> 6 (50%) -> 4 (33%) -> 12
                        if (currentSize === 12) newSize = 6;
                        else if (currentSize === 6) newSize = 4;
                        else newSize = 12;
                    }
                    
                    this.preferences.sizes[widgetId] = newSize;
                    
                    // Update DOM immediately with flex-basis
                    const widget = document.querySelector(`.widget-item[data-widget-id="${widgetId}"]`);
                    if (widget) {
                        const flexBasis = `calc(${(newSize / 12) * 100}% - 1.5rem)`;
                        widget.style.flexBasis = flexBasis;
                    }
                    
                    this.savePreferences();
                },

                moveWidget(widgetId, direction) {
                    const currentIndex = this.preferences.order.indexOf(widgetId);
                    if (currentIndex === -1) return;
                    
                    const newIndex = currentIndex + direction;
                    
                    // Check bounds
                    if (newIndex < 0 || newIndex >= this.preferences.order.length) return;
                    
                    // Swap elements
                    const temp = this.preferences.order[currentIndex];
                    this.preferences.order[currentIndex] = this.preferences.order[newIndex];
                    this.preferences.order[newIndex] = temp;
                    
                    this.renderWidgets();
                    this.savePreferences();
                },
                
                initSortable() {
                    const container = document.getElementById('widgets-container');
                    if (!container) return;
                    
                    // Destroy existing sortable if it exists
                    if (this.sortable) {
                        this.sortable.destroy();
                    }
                    
                    // Don't enable drag & drop on mobile
                    if (this.isMobile) {
                        return;
                    }
                    
                    this.sortable = new Sortable(container, {
                        animation: 150,
                        disabled: true,
                        handle: '.widget-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onEnd: () => {
                            this.updateOrder();
                        }
                    });
                },
                
                updateOrder() {
                    const widgets = Array.from(document.querySelectorAll('.widget-item'));
                    this.preferences.order = widgets.map(w => w.getAttribute('data-widget-id'));
                    this.savePreferences();
                },
                
                toggleWidget(widgetId) {
                    const index = this.preferences.hidden.indexOf(widgetId);
                    if (index > -1) {
                        this.preferences.hidden.splice(index, 1);
                    } else {
                        this.preferences.hidden.push(widgetId);
                    }
                    
                    this.savePreferences();
                    this.renderWidgets();
                },
                
                savePreferences() {
                    fetch('{{ route('dashboard.preferences.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.preferences)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Dashboard preferences saved');
                        }
                    })
                    .catch(error => console.error('Error saving preferences:', error));
                },
                
                resetLayout() {
                    if (confirm('{{ __('Are you sure you want to reset the dashboard layout to default?') }}')) {
                        fetch('{{ route('dashboard.preferences.reset') }}', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        })
                        .catch(error => console.error('Error resetting layout:', error));
                    }
                }
            });
            
            // Expose global functions for onclick handlers
            window.resizeWidget = (id) => Alpine.store('dashboard').resizeWidget(id);
            window.moveWidget = (id, dir) => Alpine.store('dashboard').moveWidget(id, dir);
            
            // Initial render
            Alpine.store('dashboard').renderWidgets();
            Alpine.store('dashboard').initSortable();
        });
    </script>
    @endpush

    @push('scripts')
    {{-- SortableJS Library --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background: #f3f4f6;
            border: 2px dashed #3b82f6;
        }
        
        .sortable-chosen {
            cursor: grabbing !important;
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .sortable-drag {
            opacity: 0.8;
            cursor: grabbing !important;
        }
        
        .widget-item {
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        
        /* Show grab cursor ONLY in customization mode on desktop/tablet */
        @media (min-width: 768px) {
            body.customization-active .widget-item {
                cursor: grab !important;
            }
            
            body.customization-active .widget-item:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
            
            body.customization-active .widget-item:active {
                cursor: grabbing !important;
            }
        }
        
        /* Mobile-specific styles */
        @media (max-width: 767px) {
            .widget-item {
                margin-bottom: 1rem;
            }
            
            /* Larger touch targets for mobile */
            .widget-item button {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
            }
            
            /* Ensure widgets are full width on mobile */
            .widget-item {
                grid-column: span 12 / span 12 !important;
            }
            
            /* Better spacing on mobile */
            #widgets-container {
                gap: 1rem !important;
            }
        }
        
        /* Tablet-specific adjustments */
        @media (min-width: 768px) and (max-width: 1023px) {
            .widget-item {
                min-height: 300px;
            }
        }
        
        /* Smooth transitions for responsive changes */
        .widget-item {
            transition: all 0.3s ease;
        }
        
        /* Improve scrollbar styling for widgets */
        .widget-item ::-webkit-scrollbar {
            width: 6px;
        }
        
        .widget-item ::-webkit-scrollbar-track {
            background: #f7fafc;
        }
        
        .widget-item ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        
        .widget-item ::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
    @endpush
</x-app-layout>