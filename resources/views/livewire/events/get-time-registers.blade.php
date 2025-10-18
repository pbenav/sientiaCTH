<div class="flex flex-col m-5 sm:m-10">
    <!-- Header Section -->
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Events') }}</h2>

        @if (!$isInspector && !$isTeamAdmin)
            <div class="p-4 mx-auto mt-4 w-full bg-green-200 border-2 border-green-400 rounded-lg shadow-md">
                <div class="flex items-center justify-between cursor-pointer" id="toggleButton">
                    <p class="text-xl font-bold text-red-500">¡NUEVAS FUNCIONALIDADES!</p>
                    <i id="toggleIcon" class="fas fa-chevron-down text-gray-700 transition-transform duration-300"></i>
                </div>

                <div id="collapsibleContent" class="hidden overflow-hidden transition-all duration-300">
                    <p class="mt-2 text-gray-700">
                        Se han añadido nuevas funcionalidades a la aplicación:<br>Para los usuarios:
                    <ul class="list-none space-y-2">
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            En el perfil de usuario podrás definir tu horario laboral de modo que al añadir o editar un
                            evento te dirá en qué tramo horario estás. Esto es importante de cara al cierre de los
                            eventos.
                        </li>
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Ahora cada tipo de evento tendrá un color distinto para facilitar su identificación.
                        </li>
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Se han creado eventos de día completo, para ser usados por ejemplo en días de vacaciones o
                            asuntos propios.
                        </li>
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Los eventos de día entero deberán ser autorizados por un administrador de modo que se
                            llevará la contabilidad de estos días de una forma fácil y simple.
                        </li>
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Se ha añadido una vista de calendario, para que podáis ver de un vistazo todos los eventos
                            cercanos en el tiempo.
                        </li>
                        Para los administradores
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Podrán añadir o cambiar los tipos de evento.
                        </li>
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Tendrán la posibilidad de autorizar eventos de día entero.

                        </li>
                        <li>
                            <span class="text-green-500 mr-2">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            Cada equipo mostrará solo los eventos que le correspondan.
                        </li>
                    </ul>
                    </p>

                    <p class="text-xl font-bold text-red-500 mt-4">¡IMPORTANTE!</p>
                    <p class="text-gray-700">
                        <strong>Recuerda</strong> que debes confirmar los eventos, haciendo clic en el botón
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 px-2 py-1 rounded text-lg text-white bg-green-500">
                            <i class="fas fa-check"></i>
                        </span>, una vez que hayas <u>verificado</u> que las fechas y las horas son correctas.
                        <strong>¡Gracias!</strong>
                    </p>
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
                        <i class="fa-solid fa-person-military-pointing mr-2"></i>{{ __('My Records') }}
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
                                    @if ($ev->eventType && $ev->eventType->is_all_day)
                                        <input type="checkbox" wire:click="toggleAuthorization({{ $ev->id }})"
                                            @checked($ev->is_authorized) @disabled(!$isTeamAdmin) />
                                    @endif
                                </td>

                                @if (!$isInspector || $isTeamAdmin)
                                    <td class="flex justify-center items-center p-1 md:table-cell">
                                        <div class="flex float-right">
                                            <a class="btn {{ $ev->is_open ? 'btn-blue' : 'btn-gray' }}"
                                                wire:click="edit({{ $ev }})">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a class="btn {{ $ev->is_open ? 'btn-green' : 'btn-gray' }}"
                                                wire:click="alertConfirm({{ $ev }})">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a class="btn {{ $ev->is_open ? 'btn-red' : 'btn-gray' }}"
                                                wire:click="alertDelete({{ $ev }})">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
                    {{ __('Detalles del Evento') }}
                </x-slot>

                <x-slot name="content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="md:col-span-2"><span class="font-bold">{{ __('Id') }}:</span> {{ $selectedEvent->id }}</div>
                        <div class="md:col-span-2"><span class="font-bold">{{ __('Trabajador') }}:</span> {{ $selectedEvent->user->name }} {{ $selectedEvent->user->family_name1 }}</div>

                        <div><span class="font-bold">{{ __('Inicio') }}:</span> {{ Carbon\Carbon::parse($selectedEvent->start, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') }}</div>
                        <div><span class="font-bold">{{ __('Fin') }}:</span> {{ $selectedEvent->end ? Carbon\Carbon::parse($selectedEvent->end, 'UTC')->setTimezone(config('app.timezone'))->format('d/m/y H:i:s') : '' }}</div>

                        <div><span class="font-bold">{{ __('Duración') }}:</span> {{ $selectedEvent->getPeriod() }}</div>
                        <div><span class="font-bold">{{ __('Tipo de Evento') }}:</span> {{ $selectedEvent->eventType ? $selectedEvent->eventType->name : __('Jornada Laboral') }}</div>

                        <div class="md:col-span-2"><span class="font-bold">{{ __('Observaciones') }}:</span> {{ $selectedEvent->observations }}</div>

                        <div><span class="font-bold">{{ __('Estado') }}:</span> {{ $selectedEvent->is_open ? __('Abierto') : __('Cerrado') }}</div>

                        @if ($selectedEvent->eventType && $selectedEvent->eventType->is_all_day)
                            <div>
                                <span class="font-bold">{{ __('Autorizado') }}:</span>
                                @if ($selectedEvent->is_authorized)
                                    {{ __('Sí') }}
                                    @if ($selectedEvent->authorizedBy)
                                        ({{ __('por') }} {{ $selectedEvent->authorizedBy->name }} {{ $selectedEvent->authorizedBy->family_name1 }})
                                    @endif
                                @else
                                    {{ __('No') }}
                                @endif
                            </div>
                        @endif

                        @if ($isTeamAdmin)
                            <hr class="md:col-span-2">
                            <div class="md:col-span-2 text-xs text-gray-600">
                                <div><span class="font-bold">{{ __('Creado el') }}:</span> {{ $selectedEvent->created_at->format('d/m/y H:i:s') }}</div>
                                <div><span class="font-bold">{{ __('Actualizado el') }}:</span> {{ $selectedEvent->updated_at->format('d/m/y H:i:s') }}</div>
                            </div>
                        @endif
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <x-jet-secondary-button wire:click="$set('showEventModal', false)">
                        {{ __('Cerrar') }}
                    </x-jet-secondary-button>
                </x-slot>
            </x-jet-dialog-modal>
        @endif


        <!-- SweetAlert Scripts -->
        @push('scripts')
            <script>
                Livewire.on('alert', function(message) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('sweetalert.alert.title') }}",
                        text: message,
                        timer: 1500,
                        timerProgressBar: true
                    });
                });

                Livewire.on('alertFail', function(message) {
                    Swal.fire({
                        icon: 'info',
                        title: "{{ __('sweetalert.alert_fail.title') }}",
                        text: message,
                        timer: 1500,
                        timerProgressBar: true
                    });
                });

                Livewire.on('confirmConfirmation', function(event) {
                    if ((event.is_open && event.end !== null) || {{ $isTeamAdmin ? 1 : 0 }}) {
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
                    }
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
