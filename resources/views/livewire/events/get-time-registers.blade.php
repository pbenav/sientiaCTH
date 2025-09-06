<div class="flex flex-col m-5 sm:m-10">
    <!-- Header Section -->
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Events') }}</h2>

        @if (!$isInspector && !$isTeamAdmin)
            <div class="p-2 mx-auto mt-2 w-full bg-green-200 border-2">
                <p class="text-lg text-red-500">¡IMPORTANTE!</p>
                <p class="flex-auto">
                    <strong>Recuerda</strong> que debes confirmar los eventos, haciendo clic en el botón
                    <span class="w-10 h-8 px-2 py-1 rounded text-lg text-center text-white bg-green-500">
                        <i class="fas fa-check"></i>
                    </span>, una vez que hayas <u>verificado</u> que las fechas y las horas son correctas.
                    <strong>¡Gracias!</strong>
                </p>
            </div>
        @endif
    </x-slot>

    <!-- Information Alert -->
    @if (session('info'))
        <div class="flex items-center px-4 py-3 text-sm font-bold text-white bg-blue-500" role="alert">
            <svg class="mr-2 w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
            </svg>
            <p>{{ __(session('info')) }}</p>
        </div>
    @endif

    <!-- Event List Section -->
    <div class="mx-auto w-full max-w-6xl" wire:init="loadEvents">

        <!-- Filters Modal -->
        <x-setfilters :isteamadmin="$isTeamAdmin" :isinspector="$isInspector"></x-setfilters>

        <div class="flex flex-row flex-wrap">
            <!-- Add Event Modal and Button -->
            @livewire('add-event')
            @if (!$isInspector || $isTeamAdmin)
                <div class="pl-0 mx-auto w-48 sm:mx-0">
                    <x-jet-button class="justify-center w-full h-16 bg-green-500 hover:bg-green-600 disabled:bg-gray-500"
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
                <x-jet-button class="w-48 h-8 justify-center whitespace-nowrap {{ $filtered ? 'bg-green-500' : 'disabled' }}"
                    wire:click="unsetFilter">
                    <i class="fa-solid fa-filter-slash"></i>
                    <i class="mr-2 fas fa-x"></i>{{ __('Unset filter') }}
                </x-jet-button>
            </div>

            <!-- Search Input and Checkbox -->
            <div class="flex flex-nowrap">
                <div class="w-auto">
                    <x-jet-input class="w-full h-8" placeholder="{{ __('Search') }}" type="text" wire:model="search" />
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
                        <tr class="block md:table-row">
                            <th class="p-1 text-center text-white bg-gray-600 md:table-cell">{{ __('Id') }}</th>

                            @if ($isTeamAdmin || $isInspector)
                                <th class="p-1 text-center text-white bg-gray-600 md:table-cell" wire:click="order('name')">
                                    {{ __('Worker') }}
                                    <i class="float-right mt-1 fas fa-sort"></i>
                                </th>
                            @endif

                            <th class="p-1 text-left text-white bg-gray-600 md:table-cell" wire:click="order('start')">
                                {{ __('Start') }}
                                <i class="float-right mt-1 fas fa-sort"></i>
                            </th>

                            <th class="p-1 text-left text-white bg-gray-600 md:table-cell" wire:click="order('end')">
                                {{ __('End') }}
                                <i class="float-right mt-1 fas fa-sort"></i>
                            </th>

                            <th class="p-1 text-left text-white bg-gray-600 md:table-cell" wire:click="order('description')">
                                {{ __('Description') }}
                                <i class="float-right mt-1 fas fa-sort"></i>
                            </th>

                            <th class="p-1 text-center text-white bg-gray-600 md:table-cell">{{ __('Duration') }}</th>
                            <th class="p-1 text-center text-white bg-gray-600 md:table-cell">{{ __('Authorized') }}</th>

                            @if (!$isInspector || $isTeamAdmin)
                                <th class="p-1 text-center text-white bg-gray-600 md:table-cell">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="block md:table-row-group">
                        @foreach ($events as $ev)
                            <tr class="block border md:table-row">
                                <td class="p-1 text-center md:table-cell" style="background-color: {{ $ev->eventType->color ?? 'transparent' }}; color: {{ $ev->eventType && $this->isDark($ev->eventType->color) ? 'white' : 'black' }}">
                                    {{ $ev->id }}
                                </td>

                                @if ($isTeamAdmin || $isInspector)
                                    <td class="p-1 text-left md:table-cell">{{ $ev->user->name . ' ' . $ev->user->family_name1 }}</td>
                                @endif

                                <td class="p-1 text-left md:table-cell">{{ Carbon\Carbon::parse($ev->start)->format('d/m/y H:i:s') }}</td>
                                <td class="p-1 text-left md:table-cell">{{ $ev->end ? Carbon\Carbon::parse($ev->end)->format('d/m/y H:i:s') : '' }}</td>
                                <td class="p-1 text-left md:table-cell">
                                    @if ($ev->eventType)
                                        <span class="inline-block w-3 h-3 mr-2 rounded-full" style="background-color: {{ $ev->eventType->color }}"></span>
                                        <span>{{ $ev->eventType->name }}</span>
                                    @else
                                        {{ __($ev->description) }}
                                    @endif
                                </td>
                                <td class="p-1 text-center md:table-cell">{{ $ev->getPeriod() }}</td>
                                <td class="p-1 text-center md:table-cell">
                                    @if ($ev->eventType && $ev->eventType->is_all_day)
                                        <x-jet-checkbox
                                            wire:click="toggleAuthorization({{ $ev->id }})"
                                            :checked="$ev->is_authorized"
                                            :disabled="!$isTeamAdmin"
                                        />
                                    @endif
                                </td>

                                @if (!$isInspector || $isTeamAdmin)
                                    <td class="flex justify-center items-center p-1 md:table-cell">
                                        <div class="flex float-right">
                                            <a class="btn {{ $ev->is_open ? 'btn-blue' : 'btn-gray' }}" wire:click="edit({{ $ev }})">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a class="btn {{ $ev->is_open ? 'btn-green' : 'btn-gray' }}" wire:click="alertConfirm({{ $ev }})">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a class="btn {{ $ev->is_open ? 'btn-red' : 'btn-gray' }}" wire:click="alertDelete({{ $ev }})">
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

        <!-- SweetAlert Scripts -->
        @push('scripts')
            <script>
                Livewire.on('alert', function(message) {
                    Swal.fire({
                        icon: 'success',
                        title: "{{ __('OK, perfect!') }}",
                        text: message,
                        timer: 1500,
                        timerProgressBar: true
                    });
                });

                Livewire.on('alertFail', function(message) {
                    Swal.fire({
                        icon: 'info',
                        title: "{{ __('Ups!. Something happened. Check your data!') }}",
                        text: message,
                        timer: 1500,
                        timerProgressBar: true
                    });
                });

                Livewire.on('confirmConfirmation', function(event) {
                    if ((event.is_open && event.end !== null) || {{ $isTeamAdmin ? 1 : 0 }}) {
                        Swal.fire({
                            title: "{{ __('Are you sure?') }}",
                            text: "{{ __('You won\'t be able to undo this action!') }}",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: "{{ __('Yes, confirm!') }}",
                            cancelButtonText: "{{ __('Cancel') }}",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Livewire.emit('confirm', event);
                            }
                        });
                    }
                });

                Livewire.on('deleteConfirmation', function(event) {
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('You won\'t be able to undo this action!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: "{{ __('Yes, delete!') }}",
                        cancelButtonText: "{{ __('Cancel') }}",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.emit('delete', event);
                        }
                    });
                });
            </script>
        @endpush
    </div>
</div>
