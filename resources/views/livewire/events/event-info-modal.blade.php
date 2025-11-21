<div>
    <!-- Event Info Modal -->
    @if($showModal && $eventData)
        <x-jet-dialog-modal wire:model="showModal" maxWidth="3xl">
            <x-slot name="title">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-indigo-100 p-2 rounded-full">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Event Details') }}</h3>
                            <p class="text-sm text-gray-500">#{{ $eventData['id'] }}</p>
                        </div>
                    </div>
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
            </x-slot>

            <x-slot name="content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        @if(isset($eventData['event_type']))
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Event Type') }}</label>
                            <div class="mt-1 flex items-center">
                                <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $eventData['event_type']['color'] ?? '#3788d8' }}"></span>
                                <span class="text-sm font-medium text-gray-900">{{ $eventData['event_type']['name'] ?? __('N/A') }}</span>
                            </div>
                        </div>
                        @endif

                        @if(isset($eventData['work_center']))
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Work Center') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $eventData['work_center']['name'] ?? __('N/A') }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Start') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($eventData['start'])
                                    {{ \Carbon\Carbon::parse($eventData['start'])->format('d/m/Y H:i') }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('End') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($eventData['end'])
                                    {{ \Carbon\Carbon::parse($eventData['end'])->format('d/m/Y H:i') }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</label>
                            <p class="mt-1 text-lg font-bold text-indigo-600">
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
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>

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

                        @if(isset($eventData['created_at']) || isset($eventData['updated_at']))
                        <div class="pt-4 border-t border-gray-200 space-y-2">
                            @if(isset($eventData['created_at']))
                            <div>
                                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Created') }}</label>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($eventData['created_at'])->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif

                            @if(isset($eventData['updated_at']))
                            <div>
                                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Updated') }}</label>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($eventData['updated_at'])->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Description (Full Width) -->
                    @if(isset($eventData['description']) && $eventData['description'])
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Description') }}</label>
                        <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-700">{{ $eventData['description'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                @if(!$eventData['is_open'])
                    <div class="mt-6 rounded-md bg-yellow-50 p-4 border border-yellow-100">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800">{{ __('Event Closed') }}</h3>
                                <p class="mt-1 text-sm text-yellow-700">{{ __('This event is closed and cannot be modified.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="closeModal" wire:loading.attr="disabled">
                    {{ __('Close') }}
                </x-jet-secondary-button>
            </x-slot>
        </x-jet-dialog-modal>
    @endif
</div>