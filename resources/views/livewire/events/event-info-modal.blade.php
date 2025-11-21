<div>
    <!-- Event Info Modal -->
    @if($showModal && $eventData)
        <x-jet-dialog-modal wire:model="showModal" maxWidth="lg">
            <x-slot name="title">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div class="flex items-center space-x-3">
                        <div class="bg-indigo-100 p-2 rounded-full">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Event Details') }}</h3>
                    </div>
                    <div class="flex items-center">
                        @if($eventData['is_open'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                {{ __('Open') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                {{ __('Closed') }}
                            </span>
                        @endif
                    </div>
                </div>
            </x-slot>

            <x-slot name="content">
                <div class="space-y-6">
                    <!-- Main Info -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Id') }}</label>
                                <p class="text-sm font-medium text-gray-900">#{{ $eventData['id'] }}</p>
                            </div>
                            @if(isset($eventData['event_type']))
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Event Type') }}</label>
                                <div class="flex items-center mt-1">
                                    <span class="w-3 h-3 rounded-full mr-2 shadow-sm" style="background-color: {{ $eventData['event_type']['color'] ?? '#3788d8' }}"></span>
                                    <span class="text-sm font-medium text-gray-900">{{ $eventData['event_type']['name'] ?? __('N/A') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Description') }}</label>
                        <div class="mt-1 p-3 bg-white border border-gray-200 rounded-md shadow-sm">
                            <p class="text-sm text-gray-700">{{ $eventData['description'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Timing -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Start') }}</label>
                            <p class="text-sm text-gray-900 mt-1">
                                @if($eventData['start'])
                                    {{ \Carbon\Carbon::parse($eventData['start'])->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('End') }}</label>
                            <p class="text-sm text-gray-900 mt-1">
                                @if($eventData['end'])
                                    {{ \Carbon\Carbon::parse($eventData['end'])->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">{{ __('N/A') }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</label>
                            <p class="text-sm font-bold text-indigo-600 mt-1">
                                @if($eventData['start'] && $eventData['end'])
                                    @php
                                        $start = \Carbon\Carbon::parse($eventData['start']);
                                        $end = \Carbon\Carbon::parse($eventData['end']);
                                        $duration = $start->diff($end);
                                        $hours = $duration->h + ($duration->days * 24);
                                        $minutes = $duration->i;
                                    @endphp
                                    {{ sprintf('%02d:%02d', $hours, $minutes) }}
                                @else
                                    <span class="text-gray-400">{{ __('N/A') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="border-t border-gray-100 pt-4 grid grid-cols-2 gap-4">
                        @if(isset($eventData['work_center']))
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Work Center') }}</label>
                            <p class="text-sm text-gray-900 mt-1 flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $eventData['work_center']['name'] ?? __('N/A') }}
                            </p>
                        </div>
                        @endif

                        @if(isset($eventData['authorized']))
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Authorization') }}</label>
                            <div class="mt-1">
                                @if($eventData['authorized'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ __('Authorized') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ __('Pending') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Timestamps -->
                    <div class="bg-gray-50 rounded p-3 text-xs text-gray-500 flex justify-between">
                        @if(isset($eventData['created_at']))
                            <span>{{ __('Created') }}: {{ \Carbon\Carbon::parse($eventData['created_at'])->format('d/m/Y H:i') }}</span>
                        @endif
                        @if(isset($eventData['updated_at']))
                            <span>{{ __('Updated') }}: {{ \Carbon\Carbon::parse($eventData['updated_at'])->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>

                    @if(!$eventData['is_open'])
                        <div class="rounded-md bg-yellow-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        {{ __('Event Closed') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>
                                            {{ __('This event is closed and cannot be modified.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="closeModal" wire:loading.attr="disabled">
                    {{ __('Close') }}
                </x-jet-secondary-button>
            </x-slot>
        </x-jet-dialog-modal>
    @endif
</div>