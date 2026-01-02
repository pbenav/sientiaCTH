<div class="h-full flex flex-col">
    @if($events->isEmpty())
        <div class="flex-1 flex items-center justify-center text-gray-500 p-4">
            {{ __('No recent activity') }}
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Event') }}</th>
                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('IP Address') }}</th>
                        <th class="px-1 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('GPS Location') }}">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </th>
                        <th class="px-1 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="{{ __('NFC Validation') }}">
                            <i class="fas fa-wifi text-sm"></i>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($events as $event)
                        @php
                            $timezone = $event->getEventTimezone($event);
                            $localStart = $event->utcToTeamTimezone($event->start, $timezone);
                        @endphp
                        <tr class="hover:bg-gray-50 cursor-pointer transition-colors" onclick="window.location.href='{{ route('events', ['event_id' => $event->id]) }}'">
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 font-mono">
                                #{{ $event->id }}
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ $localStart->format('H:i') }}</span>
                                    <span class="text-xs text-gray-400">{{ $localStart->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-xs sm:text-sm font-medium text-gray-900">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1">
                                    <span class="truncate">{{ $event->title ?? optional($event->eventType)->name ?? __('Unknown') }}</span>
                                    <div class="flex items-center gap-1">
                                        @if($event->is_open)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('Active') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- IP Address Column (Text) --}}
                            <td class="px-2 sm:px-4 py-3 text-xs sm:text-sm text-gray-600 font-mono">
                                {{ $event->ip_address ?? '-' }}
                            </td>
                            {{-- GPS Column --}}
                            <td class="px-1 py-3 whitespace-nowrap text-center text-sm">
                                @if($event->location_start || $event->location_end)
                                    @php
                                        $location = $event->location_start ?? $event->location_end;
                                        $lat = is_string($location) ? json_decode($location, true)['latitude'] ?? null : ($location['latitude'] ?? null);
                                        $lng = is_string($location) ? json_decode($location, true)['longitude'] ?? null : ($location['longitude'] ?? null);
                                    @endphp
                                    @if($lat && $lng)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}" 
                                           target="_blank"
                                           onclick="event.stopPropagation();"
                                           class="text-green-600 hover:text-green-800 transition-colors" 
                                           title="{{ __('View on map') }}: {{ $lat }}, {{ $lng }}">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    @else
                                        <span class="text-green-600" title="{{ __('Location data available') }}">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-300" title="{{ __('No location data') }}">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                @endif
                            </td>
                            {{-- NFC Column --}}
                            <td class="px-1 py-3 whitespace-nowrap text-center text-sm">
                                @if($event->nfc_tag_id)
                                    <span class="text-blue-600" title="{{ __('NFC Tag') }}: {{ $event->nfc_tag_id }}">
                                        <i class="fas fa-wifi"></i>
                                    </span>
                                @else
                                    <span class="text-gray-300" title="{{ __('No NFC data') }}">
                                        <i class="fas fa-wifi"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
