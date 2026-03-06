<div>
    <!-- Event Info Modal -->
    @if ($showModal && $eventData)
        <x-jet-dialog-modal wire:model="showModal" maxWidth="2xl">
            <x-slot name="title">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-100 p-2 rounded-full">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">
                                {{ __('Event Details') }} <span
                                    class="text-gray-400 font-normal text-sm">#{{ $eventData['id'] }}</span>
                            </h3>
                            <p class="text-xs text-gray-500">{{ $eventData['user']['name'] ?? '' }}
                                {{ $eventData['user']['family_name1'] ?? '' }}</p>
                        </div>
                    </div>
                    @if ($eventData['is_open'])
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ __('Open') }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ __('Closed') }}
                        </span>
                    @endif
                </div>
            </x-slot>

            <x-slot name="content">
                <!-- Main Info Grid (2 columns, compact) -->
                <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <!-- Event Type -->
                    @if (isset($eventData['event_type']))
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">{{ __('Event Type') }}</label>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                    style="background-color: {{ $eventData['event_type']['color'] ?? '#3788d8' }}"></span>
                                <span
                                    class="font-medium text-gray-950 truncate">{{ $eventData['event_type']['name'] ?? __('N/A') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Duration -->
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">{{ __('Duration') }}</label>
                        <p class="font-bold text-indigo-600">{{ $eventData['duration'] ?? __('N/A') }}</p>
                    </div>

                    <!-- Work Center -->
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">{{ __('Work Center') }}</label>
                        @if (isset($eventData['work_center']) && $eventData['work_center'])
                            <p class="text-gray-950 truncate">{{ $eventData['work_center']['name'] ?? __('N/A') }}</p>
                        @else
                            <p class="text-gray-500 italic text-sm">{{ __('No work center assigned') }}</p>
                        @endif
                    </div>

                    <!-- Authorization -->
                    @if (isset($eventData['event_type']['is_authorizable']) && $eventData['event_type']['is_authorizable'])
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">{{ __('Authorization') }}</label>
                            @if ($eventData['authorized'])
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ __('Authorized') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('Pending') }}
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Exceptional Event Indicator -->
                    @if (isset($eventData['is_exceptional']) && $eventData['is_exceptional'])
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">{{ __('Event Type') }}</label>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                {{ __('Exceptional Event') }}
                            </span>
                        </div>
                    @endif

                    <!-- Start & End Time (Combined) -->
                    <div class="col-span-2 sm:col-span-1">
                        <label class="text-xs text-gray-600 block mb-1">{{ __('Schedule') }}</label>
                        <div class="flex flex-col gap-1.5 min-w-max">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-950">{{ $eventData['start'] ?? __('N/A') }}</span>
                            </div>
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-950">
                                    @if ($eventData['is_open'])
                                        <span class="text-indigo-600 italic text-xs">{{ __('En curso...') }}</span>
                                    @else
                                        {{ $eventData['end'] ?? __('N/A') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description (if exists) -->
                @if (isset($eventData['description']) && $eventData['description'])
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <label class="text-xs text-gray-600 block mb-1">{{ __('Description') }}</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-sm text-gray-700">{{ $eventData['description'] }}</p>
                        </div>
                    </div>
                @endif

                <!-- Validation - Inline Icons -->
                <div class="flex items-center gap-4 text-xs pt-4 border-t border-gray-200 mt-4">
                    <span class="font-semibold text-gray-600 uppercase tracking-wide">{{ __('Validation') }}:</span>

                    <!-- GPS -->
                    @php
                        $hasGPS =
                            isset($eventData['latitude']) &&
                            $eventData['latitude'] &&
                            isset($eventData['longitude']) &&
                            $eventData['longitude'];
                    @endphp
                    @if ($hasGPS)
                        <a href="https://www.google.com/maps?q={{ $eventData['latitude'] }},{{ $eventData['longitude'] }}"
                            target="_blank"
                            class="flex items-center gap-1 text-green-600 hover:text-green-700 transition-colors"
                            title="GPS: {{ number_format($eventData['latitude'], 4) }}, {{ number_format($eventData['longitude'], 4) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">GPS</span>
                        </a>
                    @else
                        <span class="flex items-center gap-1 text-gray-300" title="{{ __('GPS not available') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                            </svg>
                            <span>GPS</span>
                        </span>
                    @endif

                    <!-- NFC -->
                    @php
                        $hasNFC = isset($eventData['nfc_tag_id']) && $eventData['nfc_tag_id'];
                    @endphp
                    <span class="flex items-center gap-1 {{ $hasNFC ? 'text-blue-600' : 'text-gray-300' }}"
                        title="{{ $hasNFC ? 'NFC: ' . $eventData['nfc_tag_id'] : __('NFC not available') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                            </path>
                        </svg>
                        <span class="{{ $hasNFC ? 'font-medium' : '' }}">NFC</span>
                    </span>

                    <!-- IP -->
                    @php
                        $hasIP = isset($eventData['ip_address']) && $eventData['ip_address'];
                    @endphp
                    <span class="flex items-center gap-1 {{ $hasIP ? 'text-purple-600' : 'text-gray-300' }}"
                        title="{{ $hasIP ? 'IP: ' . $eventData['ip_address'] : __('IP not available') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                            </path>
                        </svg>
                        <span
                            class="{{ $hasIP ? 'font-medium font-mono' : '' }}">{{ $hasIP ? $eventData['ip_address'] : 'IP' }}</span>
                    </span>
                </div>

                <!-- Metadata - Compact -->
                @if (isset($eventData['created_at']) || isset($eventData['updated_at']))
                    <div class="flex gap-4 text-xs text-gray-600 pt-2 mt-2">
                        @if (isset($eventData['created_at']))
                            <span>{{ __('Created') }}: {{ $eventData['created_at'] }}</span>
                        @endif
                        @if (isset($eventData['updated_at']))
                            <span>{{ __('Updated') }}: {{ $eventData['updated_at'] }}</span>
                        @endif
                    </div>
                @endif

                <!-- Closed Event Warning -->
                @if (!$eventData['is_open'])
                    <div class="mt-4 rounded-md bg-yellow-50 p-3 border border-yellow-100">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800">{{ __('Event Closed') }}</h3>
                                <p class="mt-1 text-xs text-yellow-700">
                                    {{ __('This event is closed and cannot be modified.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <div class="flex items-center justify-between w-full">
                    <div>
                        @if (!$eventData['is_open'] && $eventData['user_id'] == auth()->id())
                            <x-jet-button onclick="Livewire.emit('openReopeningModal', {{ $eventData['id'] }})"
                                class="bg-orange-600 hover:bg-orange-700">
                                {{ __('Request Reopening') }}
                            </x-jet-button>
                        @endif
                    </div>
                    <x-jet-secondary-button wire:click="closeModal" wire:loading.attr="disabled">
                        {{ __('Close') }}
                    </x-jet-secondary-button>
                </div>
            </x-slot>
        </x-jet-dialog-modal>
    @endif

    {{-- Reopening Request Component --}}
    @livewire('events.request-event-reopening')
</div>
