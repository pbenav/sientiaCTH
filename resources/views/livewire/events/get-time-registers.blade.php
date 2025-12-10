<div class="flex flex-col m-5 sm:m-10">
    <!-- Header Section -->
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Events') }}</h2>

    </x-slot>

    <!-- Information Alert -->
    @if (session('info'))
        <div class="flex items-center px-4 py-3 text-sm font-medium text-blue-800 bg-blue-100 border border-blue-200 rounded-lg" role="alert">
            <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z"/></svg>
            <p>{{ __(session('info')) }}</p>
        </div>
    @endif

    @if (session()->has('alertFail'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'info',
                    title: "{{ __('sweetalert.alert_fail.title') }}",
                    text: "{{ session('alertFail') }}",
                    showConfirmButton: true,
                    confirmButtonText: "{{ __('sweetalert.ok_button') }}",
                });
            });
        </script>
    @endif

    <!-- Event List Section -->
    <div class="mx-auto w-full xl:max-w-[90rem]" wire:init="loadEvents">

        <!-- Filters Modal -->
        <x-setfilters :isteamadmin="$isTeamAdmin" :isinspector="$isInspector" :eventTypes="$eventTypes" :teamUserList="$teamUserList"></x-setfilters>

        <!-- Add Event Button -->
        <div class="flex flex-row flex-wrap gap-3 mb-4">
            @livewire('add-event')
            @if (!$isInspector || $isTeamAdmin)
                <x-jet-button
                    class="justify-center h-12 bg-green-600 hover:bg-green-700 focus:ring-green-500"
                    wire:click="$emitTo('add-event', 'add', '1')">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    {{ __('Add event') }}
                </x-jet-button>
            @endif
        </div>

        <!-- Filter and Search Controls -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
            <div class="flex flex-wrap gap-3">
                <!-- Filter Buttons -->
                <x-jet-button class="h-10" wire:click="setFilter">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    {{ __('Set filter') }}
                </x-jet-button>

                <x-jet-button
                    class="h-10 {{ $filtered ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400' }}"
                    wire:click="unsetFilter">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ __('Unset filter') }}
                </x-jet-button>

                <x-jet-button class="h-10 bg-gray-600 hover:bg-gray-700" wire:click="$refresh">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    {{ __('Refresh') }}
                </x-jet-button>

                @if ($isTeamAdmin)
                    <x-jet-button class="h-10 {{ $showOnlyMine ? 'bg-indigo-600' : 'bg-gray-600' }}" wire:click="filterOnlyMine">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        {{ $showOnlyMine ? __('All Records') : __('My Records') }}
                    </x-jet-button>
                @endif

                <!-- Search Input -->
                <div class="flex-1 min-w-[200px]">
                    <x-jet-input class="w-full h-10" placeholder="{{ __('Search') }}" type="text" wire:model="search" />
                </div>

                <!-- Not Confirmed Checkbox -->
                <label class="flex items-center space-x-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <x-jet-checkbox class="w-5 h-5" wire:model="confirmed" wire:click="$set('filtered', false)" />
                    <span class="text-sm font-medium text-gray-700">{{ __('Not confirmed') }}</span>
                </label>

                <!-- Records Per Page -->
                <div class="flex items-center space-x-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="text-sm text-gray-600">{{ __('Show') }}</span>
                    <select wire:model='qtytoshow' class="h-8 border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-600">{{ __('records') }}</span>
                </div>
            </div>
        </div>

        <!-- Events Table/Cards -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if (count($events))
                <!-- Desktop/Tablet Table View (hidden only on very small mobile < 480px) -->
                <div class="hidden xs:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('Id') }}
                                </th>

                                @if ($isTeamAdmin || $isInspector)
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="order('name')">
                                        <div class="flex items-center justify-between">
                                            {{ __('Worker') }}
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                        </div>
                                    </th>
                                @endif

                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="order('start')">
                                    <div class="flex items-center justify-between">
                                        {{ __('Start') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                    </div>
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="order('end')">
                                    <div class="flex items-center justify-between">
                                        {{ __('End') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                    </div>
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="order('description')">
                                    <div class="flex items-center justify-between">
                                        {{ __('Description') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                    </div>
                                </th>

                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('Duration') }}
                                </th>

                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('Authorized') }}
                                </th>

                                @if (!$isInspector || $isTeamAdmin)
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                @endif
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($events as $ev)
                                @php
                                    $eventColor = $this->getEventColor($ev);
                                    $isDark = $this->isDark($eventColor);
                                @endphp
                                <tr wire:key="event-desktop-{{ $ev->id }}" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap cursor-pointer" wire:click="showEventModal({{ $ev->id }})">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shadow-sm" 
                                              style="background-color: {{ $eventColor }}; color: {{ $isDark ? 'white' : 'black' }}">
                                            #{{ $ev->id }}
                                        </span>
                                    </td>

                                    @if ($isTeamAdmin || $isInspector)
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $ev->user->name }} {{ $ev->user->family_name1 }}
                                        </td>
                                    @endif

                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ Carbon\Carbon::parse($ev->start, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i') }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ $ev->end ? Carbon\Carbon::parse($ev->end, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i') : '-' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <span class="inline-block w-3 h-3 rounded-full mr-2 flex-shrink-0" style="background-color: {{ $eventColor }}"></span>
                                            <span class="truncate">
                                                @if ($ev->eventType)
                                                    {{ $ev->eventType->name }}
                                                    @if($ev->is_exceptional)
                                                        <span class="text-xs text-red-600 ml-1">({{ __('Exceptional') }})</span>
                                                    @endif
                                                @else
                                                    {{ __($ev->description) }}
                                                    @if($ev->is_exceptional)
                                                        <span class="text-xs text-red-600 ml-1">({{ __('Exceptional') }})</span>
                                                    @endif
                                                @endif
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                                        {{ $ev->getPeriod() }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if ($ev->eventType && $ev->eventType->is_authorizable)
                                            <input type="checkbox" 
                                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                   wire:click="toggleAuthorization({{ $ev->id }})"
                                                   @checked($ev->is_authorized) 
                                                   @disabled(!$isTeamAdmin) />
                                        @endif
                                    </td>

                                    @if (!$isInspector || $isTeamAdmin)
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <div class="flex justify-center space-x-1">
                                                <button class="p-2 rounded {{ $ev->is_open ? 'text-blue-600 hover:bg-blue-50' : 'text-gray-400' }}"
                                                    wire:click="edit({{ $ev->id }})"
                                                    @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif
                                                    title="{{ __('Edit') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <button class="p-2 rounded {{ $ev->is_open ? 'text-green-600 hover:bg-green-50' : 'text-gray-400' }}"
                                                    wire:click="alertConfirm({{ $ev->id }})"
                                                    @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif
                                                    title="{{ __('Confirm') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                                <button class="p-2 rounded {{ $ev->is_open ? 'text-red-600 hover:bg-red-50' : 'text-gray-400' }}"
                                                    wire:click="alertDelete({{ $ev->id }})"
                                                    @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif
                                                    title="{{ __('Delete') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (visible only on very small screens < 480px) -->
                <div class="block xs:hidden divide-y divide-gray-200">
                    @foreach ($events as $ev)
                        @php
                            $eventColor = $this->getEventColor($ev);
                            $isDark = $this->isDark($eventColor);
                        @endphp
                        <div wire:key="event-mobile-{{ $ev->id }}" class="p-4 hover:bg-gray-50">
                            <!-- Header with ID and Actions -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shadow-sm cursor-pointer" 
                                      style="background-color: {{ $eventColor }}; color: {{ $isDark ? 'white' : 'black' }}"
                                      wire:click="showEventModal({{ $ev->id }})">
                                    #{{ $ev->id }}
                                </span>
                                
                                @if (!$isInspector || $isTeamAdmin)
                                    <div class="flex space-x-1">
                                        <button class="p-2 rounded {{ $ev->is_open ? 'text-blue-600 hover:bg-blue-50' : 'text-gray-400' }}"
                                            wire:click="edit({{ $ev->id }})"
                                            @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button class="p-2 rounded {{ $ev->is_open ? 'text-green-600 hover:bg-green-50' : 'text-gray-400' }}"
                                            wire:click="alertConfirm({{ $ev->id }})"
                                            @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                        <button class="p-2 rounded {{ $ev->is_open ? 'text-red-600 hover:bg-red-50' : 'text-gray-400' }}"
                                            wire:click="alertDelete({{ $ev->id }})"
                                            @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Event Details -->
                            <div class="space-y-2 text-sm">
                                @if ($isTeamAdmin || $isInspector)
                                    <div class="flex items-center text-gray-700">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        <span class="font-medium">{{ $ev->user->name }} {{ $ev->user->family_name1 }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ $eventColor }}"></span>
                                    <span class="text-gray-900">
                                        @if ($ev->eventType)
                                            {{ $ev->eventType->name }}
                                            @if($ev->is_exceptional)
                                                <span class="text-xs text-red-600 ml-1">({{ __('Exceptional') }})</span>
                                            @endif
                                        @else
                                            {{ __($ev->description) }}
                                            @if($ev->is_exceptional)
                                                <span class="text-xs text-red-600 ml-1">({{ __('Exceptional') }})</span>
                                            @endif
                                        @endif
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                                    <div>
                                        <span class="font-semibold">{{ __('Start') }}:</span>
                                        {{ Carbon\Carbon::parse($ev->start, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i') }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">{{ __('End') }}:</span>
                                        {{ $ev->end ? Carbon\Carbon::parse($ev->end, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i') : '-' }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-semibold text-gray-600">{{ __('Duration') }}:</span>
                                        <span class="text-gray-900 font-medium">{{ $ev->getPeriod() }}</span>
                                    </div>
                                    @if ($ev->eventType && $ev->eventType->is_authorizable)
                                        <div class="flex items-center">
                                            <span class="font-semibold text-gray-600 mr-2">{{ __('Authorized') }}:</span>
                                            <input type="checkbox" 
                                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                   wire:click="toggleAuthorization({{ $ev->id }})"
                                                   @checked($ev->is_authorized) 
                                                   @disabled(!$isTeamAdmin) />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($events->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">{{ $events->links() }}</div>
                @endif
            @else
                <div class="px-6 py-12 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="mt-2 text-sm font-medium">{{ __('No records found') }}</p>
                </div>
            @endif
        </div>

        <!-- Livewire Component to Edit Event -->
        @livewire('edit-event')

        <!-- Event Details Modal -->
        @if ($selectedEvent)
            <x-jet-dialog-modal wire:model="showEventModal" maxWidth="3xl">
                <x-slot name="title">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center space-x-3">
                            <div class="bg-indigo-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('Event Details') }}</h3>
                                <p class="text-sm text-gray-500">#{{ $selectedEvent->id }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($selectedEvent->is_open)
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Worker') }}</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedEvent->user->name }} {{ $selectedEvent->user->family_name1 }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Start') }}</label>
                                <p class="mt-1 text-sm text-gray-900">{{ Carbon\Carbon::parse($selectedEvent->start, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('End') }}</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedEvent->end ? Carbon\Carbon::parse($selectedEvent->end, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') : '-' }}</p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Event Type') }}</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedEvent->eventType ? $selectedEvent->eventType->name : __('Work Shift') }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</label>
                                <p class="mt-1 text-lg font-bold text-indigo-600">{{ $selectedEvent->getPeriod() }}</p>
                            </div>

                            @if ($selectedEvent->eventType && $selectedEvent->eventType->is_authorizable)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Authorized') }}</label>
                                    <div class="mt-1">
                                        @if ($selectedEvent->is_authorized)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                {{ __('Yes') }}
                                                @if ($selectedEvent->authorizedBy)
                                                    ({{ $selectedEvent->authorizedBy->name }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ __('No') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Full Width -->
                        @if($selectedEvent->observations)
                            <div class="md:col-span-2">
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Observations') }}</label>
                                <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-sm text-gray-700">{{ __($selectedEvent->observations) }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($isTeamAdmin)
                            <div class="md:col-span-2 pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-2 gap-4 text-xs text-gray-500">
                                    <div>
                                        <span class="font-semibold">{{ __('Created at') }}:</span>
                                        {{ $selectedEvent->created_at->format('d/m/y H:i:s') }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">{{ __('Updated at') }}:</span>
                                        {{ $selectedEvent->updated_at->format('d/m/y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <x-jet-secondary-button wire:click="$set('showEventModal', false)">
                        {{ __('Close') }}
                    </x-jet-secondary-button>
                </x-slot>
            </x-jet-dialog-modal>
        @endif

        <!-- SweetAlert Scripts -->
        @push('scripts')
            <script>
                function showClosedEventAlert() {
                    Swal.fire({
                        icon: 'info',
                        title: "{{ __('sweetalert.event_closed.title') }}",
                        text: "{{ __('sweetalert.event_closed.text') }}",
                        confirmButtonText: "{{ __('sweetalert.ok_button') }}",
                    });
                }

                Livewire.on('alert', function(message) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('sweetalert.alert.title') }}",
                        text: message,
                        timer: 1500,
                        timerProgressBar: true
                    });
                });

                Livewire.on('incompleteEventConfirmation', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: "{{ __('sweetalert.incomplete_event_confirmation.title') }}",
                        text: "{{ __('sweetalert.incomplete_event_confirmation.text') }}",
                        confirmButtonText: "{{ __('sweetalert.incomplete_event_confirmation.confirmButtonText') }}",
                    });
                });

                Livewire.on('confirmConfirmation', function(event) {
                    Swal.fire({
                        title: "{{ __('sweetalert.confirm_confirmation.title') }}",
                        text: "{{ __('sweetalert.confirm_confirmation.text') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: "{{ __('sweetalert.confirm_confirmation.confirmButtonText') }}",
                        cancelButtonText: "{{ __('sweetalert.confirm_confirmation.cancelButtonText') }}",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.emit('confirm', event);
                        }
                    });
                });

                Livewire.on('deleteConfirmation', function(event) {
                    Swal.fire({
                        title: "{{ __('sweetalert.delete_confirmation.title') }}",
                        text: "{{ __('sweetalert.delete_confirmation.text') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: "{{ __('sweetalert.delete_confirmation.confirmButtonText') }}",
                        cancelButtonText: "{{ __('sweetalert.delete_confirmation.cancelButtonText') }}",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.emit('delete', event);
                        }
                    });
                });

                Livewire.on('modalClosed', () => {
                    console.log('Modal closed from events view');
                    // Add any additional logic if needed
                });
            </script>
        @endpush
    </div>
</div>
