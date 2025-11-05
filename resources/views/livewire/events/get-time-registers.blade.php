<div class="flex flex-col m-5 sm:m-10">
    <!-- Header Section -->
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Events') }}</h2>

        <!-- Team Announcements Section -->
        @if ($announcements->count() > 0)
            <div class="p-4 mx-auto mt-4 w-full bg-blue-100 border-2 border-blue-400 rounded-lg shadow-md" x-data="{ open: false }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <p class="text-xl font-bold text-blue-700">
                        <i class="fas fa-bullhorn mr-2"></i>
                        Anuncios del Equipo
                    </p>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-gray-700 transition-transform duration-300"></i>
                </div>

                <div x-show="open" x-transition class="mt-4 space-y-4">
                    @foreach ($announcements as $announcement)
                        <div class="p-4 bg-white rounded-lg shadow border-l-4 border-blue-500">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $announcement->title }}</h3>
                                    <div class="mt-2 prose prose-sm max-w-none text-gray-700">
                                        {!! $announcement->content !!}
                                    </div>
                                    
                                    <div class="mt-3 text-sm text-gray-500 space-y-1">
                                        @if ($announcement->start_date || $announcement->end_date)
                                            <div>
                                                <i class="far fa-calendar mr-1"></i>
                                                @if ($announcement->start_date && $announcement->end_date)
                                                    Del {{ $announcement->start_date->format('d/m/Y') }} al {{ $announcement->end_date->format('d/m/Y') }}
                                                @elseif ($announcement->start_date)
                                                    Desde {{ $announcement->start_date->format('d/m/Y') }}
                                                @else
                                                    Hasta {{ $announcement->end_date->format('d/m/Y') }}
                                                @endif
                                            </div>
                                        @endif
                                        <div>
                                            <i class="far fa-user mr-1"></i>
                                            Publicado por {{ $announcement->creator->name }} el {{ $announcement->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-slot>

    <!-- Information Alert -->
    @if (session('info'))
        <div class="flex items-center px-4 py-3 text-sm font-bold text-white bg-blue-500" role="alert">
            <svg class="mr-2 w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path
                    d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
            </svg>
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
    <div class="mx-auto w-full max-w-6xl" wire:init="loadEvents">

        <!-- Filters Modal -->
        <x-setfilters :isteamadmin="$isTeamAdmin" :isinspector="$isInspector" :eventTypes="$eventTypes" :teamUserList="$teamUserList"></x-setfilters>

        <div class="flex flex-row flex-wrap">
            <!-- Add Event Modal and Button -->
            @livewire('add-event')
            @if (!$isInspector || $isTeamAdmin)
                <div class="pl-0 mx-auto w-48 sm:mx-0">
                    <x-jet-button
                        class="justify-center w-full h-16 bg-green-500 hover:bg-green-600 disabled:bg-gray-500"
                        wire:click="$emitTo('add-event', 'add', '1')">
                        <i class="mr-2 fas fa-plus"></i> {{ __('Add event') }}
                    </x-jet-button>
                </div>
            @endif
        </div>

        <!-- Filter and Search Controls -->
        <div class="flex flex-wrap gap-2 mt-2">
            <div class="mx-auto w-48 sm:mx-0">
                <x-jet-button class="w-48 h-8 justify-center" wire:click="setFilter">
                    <i class="mr-2 fas fa-filter"></i>{{ __('Set filter') }}
                </x-jet-button>
            </div>
            <div class="mx-auto w-48 sm:mx-0">
                <x-jet-button
                    class="w-48 h-8 justify-center whitespace-nowrap {{ $filtered ? 'bg-green-500' : 'disabled' }}"
                    wire:click="unsetFilter">
                    <i class="fa-solid fa-filter-slash"></i>
                    <i class="mr-2 fas fa-x"></i>{{ __('Unset filter') }}
                </x-jet-button>
            </div>

            <div class="mx-auto w-48 sm:mx-0">
                <x-jet-button class="w-48 h-8 justify-center" wire:click="$refresh">
                    <i class="fa-solid fa-arrows-rotate mr-2"></i>{{ __('Refresh') }}
                </x-jet-button>
            </div>
            @if ($isTeamAdmin)
                <div class="mx-auto w-48 sm:mx-0">
                    <x-jet-button class="w-48 h-8 justify-center {{ $showOnlyMine ? 'bg-green-500' : '' }}"
                        wire:click="filterOnlyMine">
                        <i class="fa-solid fa-person-military-pointing mr-2"></i>
                        @if ($showOnlyMine)
                            {{ __('All Records') }}
                        @else
                            {{ __('My Records') }}
                        @endif
                    </x-jet-button>
                </div>
            @endif


            <!-- Search Input and Checkbox -->
            <div class="flex flex-nowrap">
                <div class="w-auto">
                    <x-jet-input class="w-full h-8" placeholder="{{ __('Search') }}" type="text"
                        wire:model="search" />
                </div>
                <div class="flex flex-row-reverse pt-1 ml-4 w-auto">
                    <div>
                        <x-jet-label class="pt-1" value="{{ __('Not confirmed') }}" />
                    </div>
                    <div>
                        <x-jet-checkbox class="mr-2 w-6 h-6 text-gray-600 checked:text-green-600" wire:model="confirmed"
                            wire:click="$set('filtered', false)" />
                    </div>
                </div>
            </div>

            <!-- Records Per Page Selector -->
            <div class="flex flex-nowrap mr-2 mb-2 h-8">
                <div class="p-1">{{ __('Show') }}</div>
                <select wire:model='qtytoshow' class="pt-1 mx-2 h-8 form-control">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <div class="p-1">{{ __('records') }}</div>
            </div>
        </div>

        <!-- Events Table -->
        <div>
            @if (count($events))
                <table class="block min-w-full border-collapse md:table">
                    <thead class="block md:table-header-group">
                        <tr class="md:table-row">
                            <th class="p-1 text-center text-white bg-gray-600 table-cell w-1/12">{{ __('Id') }}
                            </th>

                            @if ($isTeamAdmin || $isInspector)
                                <th class="min-w-50 p-1 text-center text-white bg-gray-600 table-cell w-3/12"
                                    wire:click="order('name')">
                                    {{ __('Worker') }}
                                    <i class="float-right mt-1 fas fa-sort"></i>
                                </th>
                            @endif

                            <th class="min-w-50 p-1 text-left text-white bg-gray-600 table-cell w-2/12"
                                wire:click="order('start')">
                                {{ __('Start') }}
                                <i class="float-right mt-1 fas fa-sort"></i>
                            </th>

                            <th class="min-w-50 p-1 text-left text-white bg-gray-600 table-cell w-2/12"
                                wire:click="order('end')">
                                {{ __('End') }}
                                <i class="float-right mt-1 fas fa-sort"></i>
                            </th>

                            <th class="min-w-50 p-1 text-left text-white bg-gray-600 table-cell w-3/12"
                                wire:click="order('description')">
                                {{ __('Description') }}
                                <i class="float-right mt-1 fas fa-sort"></i>
                            </th>

                            <th class="min-w-50 p-1 text-center text-white bg-gray-600 table-cell w-1/12">{{ __('Duration') }}
                            </th>

                            <th class="p-1 text-center text-white bg-gray-600 md:table-cell md:w-1/12">
                                {{ __('Authorized') }}
                            </th>

                            @if (!$isInspector || $isTeamAdmin)
                                <th class="hidden p-1 text-center text-white bg-gray-600 md:table-cell w-1/12">
                                    {{ __('Actions') }}
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="block md:table-row-group">
                        @foreach ($events as $ev)
                            <tr class="block border md:table-row">
                                <td class="p-1 text-center md:table-cell w-1/12 cursor-pointer hover:bg-gray-200"
                                    wire:click="showEventModal({{ $ev->id }})"
                                    style="background-color: {{ $ev->eventType->color ?? 'transparent' }}; color: {{ $ev->eventType && $this->isDark($ev->eventType->color) ? 'white' : 'black' }}">
                                    {{ $ev->id }}
                                </td>

                                @if ($isTeamAdmin || $isInspector)
                                    <td class="p-1 text-left md:table-cell w-3/12">
                                        {{ $ev->user->name }} {{ $ev->user->family_name1 }}
                                    </td>
                                @endif

                                <td class="p-1 text-left md:table-cell w-2/12">
                                    {{ Carbon\Carbon::parse($ev->start, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') }}
                                </td>

                                <td class="p-1 text-left md:table-cell w-2/12">
                                    {{ $ev->end ? Carbon\Carbon::parse($ev->end, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') : '' }}
                                </td>

                                <td class="p-1 text-left md:table-cell w-3/12">
                                    @if ($ev->eventType)
                                        <span class="inline-block w-3 h-3 mr-2 rounded-full"
                                            style="background-color: {{ $ev->eventType->color }}"></span>
                                        <span>{{ $ev->eventType->name }}</span>
                                    @else
                                        {{ __($ev->description) }}
                                    @endif
                                </td>

                                <td class="p-1 text-center md:table-cell w-1/12">{{ $ev->getPeriod() }}</td>

                                <td class="p-1 text-center md:table-cell md:w-1/12">
                                    @if ($ev->eventType && $ev->eventType->is_authorizable)
                                        <input type="checkbox" wire:click="toggleAuthorization({{ $ev->id }})"
                                            @checked($ev->is_authorized) @disabled(!$isTeamAdmin) />
                                    @endif
                                </td>

                                @if (!$isInspector || $isTeamAdmin)
                                    <td class="flex justify-center items-center p-1 md:table-cell">
                                        <div class="flex float-right">
                                            <button class="btn {{ $ev->is_open ? 'btn-blue' : 'btn-gray' }}"
                                                    wire:click="edit({{ $ev }})"
                                                    @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn {{ $ev->is_open ? 'btn-green' : 'btn-gray' }}"
                                                    wire:click="alertConfirm({{ $ev }})"
                                                    @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif>
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn {{ $ev->is_open ? 'btn-red' : 'btn-gray' }}"
                                                    wire:click="alertDelete({{ $ev }})"
                                                    @if(!$ev->is_open && !$isTeamAdmin) onclick="showClosedEventAlert()" @endif>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($events->hasPages())
                    <div class="px-6 py-3">{{ $events->links() }}</div>
                @endif
            @else
                <div class="px-4 shadow">{{ __('No records found') }}</div>
            @endif
        </div>

        <!-- Livewire Component to Edit Event -->
        @livewire('edit-event')

        <!-- Event Details Modal -->
        @if ($selectedEvent)
            <x-jet-dialog-modal wire:model="showEventModal">
                <x-slot name="title">
                    {{ __('Event Details') }}
                </x-slot>

                <x-slot name="content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="md:col-span-2"><span class="font-bold">{{ __('Id') }}:</span> {{ $selectedEvent->id }}</div>
                        <div class="md:col-span-2"><span class="font-bold">{{ __('Worker') }}:</span> {{ $selectedEvent->user->name }} {{ $selectedEvent->user->family_name1 }}</div>

                        <div><span class="font-bold">{{ __('Start') }}:</span> {{ Carbon\Carbon::parse($selectedEvent->start, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') }}</div>
                        <div><span class="font-bold">{{ __('End') }}:</span> {{ $selectedEvent->end ? Carbon\Carbon::parse($selectedEvent->end, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') : '' }}</div>

                        <div><span class="font-bold">{{ __('Duration') }}:</span> {{ $selectedEvent->getPeriod() }}</div>
                        <div><span class="font-bold">{{ __('Event Type') }}:</span> {{ $selectedEvent->eventType ? $selectedEvent->eventType->name : __('Work Shift') }}</div>

                        <div class="md:col-span-2"><span class="font-bold">{{ __('Observations') }}:</span> {{ __($selectedEvent->observations) }}</div>

                        <div><span class="font-bold">{{ __('Status') }}:</span> {{ $selectedEvent->is_open ? __('Open') : __('Closed') }}</div>

                        @if ($selectedEvent->eventType && $selectedEvent->eventType->is_authorizable)
                            <div>
                                <span class="font-bold">{{ __('Authorized') }}:</span>
                                @if ($selectedEvent->is_authorized)
                                    {{ __('Yes') }}
                                    @if ($selectedEvent->authorizedBy)
                                        ({{ __('by') }} {{ $selectedEvent->authorizedBy->name }} {{ $selectedEvent->authorizedBy->family_name1 }})
                                    @endif
                                @else
                                    {{ __('No') }}
                                @endif
                            </div>
                        @endif

                        @if ($isTeamAdmin)
                            <hr class="md:col-span-2">
                            <div class="md:col-span-2 text-xs text-gray-600">
                                <div><span class="font-bold">{{ __('Created at') }}:</span> {{ $selectedEvent->created_at->format('d/m/y H:i:s') }}</div>
                                <div><span class="font-bold">{{ __('Updated at') }}:</span> {{ $selectedEvent->updated_at->format('d/m/y H:i:s') }}</div>
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
            </script>
        @endpush
        @push('scripts')
            <script>
                const toggleButton = document.getElementById('toggleButton');
                const toggleIcon = document.getElementById('toggleIcon');
                const collapsibleContent = document.getElementById('collapsibleContent');

                toggleButton.addEventListener('click', () => {
                    const isContentVisible = !collapsibleContent.classList.contains('hidden');

                    if (isContentVisible) {
                        // Ocultar el contenido
                        collapsibleContent.classList.add('hidden');
                        toggleIcon.classList.remove('rotate-180');
                    } else {
                        // Mostrar el contenido
                        collapsibleContent.classList.remove('hidden');
                        toggleIcon.classList.add('rotate-180');
                    }
                });
            </script>
        @endpush
    </div>
</div>
