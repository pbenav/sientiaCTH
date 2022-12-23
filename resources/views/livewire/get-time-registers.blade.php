<div>
    <!-- The wire directive connects page loading with the function loadEvents to get events deferred -->
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>

    </x-slot>

    @if (session('info'))
        {{-- This div shows information attached to request if exists --}}
        <div class="flex items-center bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">
            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path
                    d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
            </svg>
            <p>{{ __(session('info')) }}</p>
        </div>
    @endif

    <!-- Event list. Main table -->
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8" wire:init="loadEvents">

        <!-- Livewire component to filter time registers" -->
        @livewire('set-time-register-filters')
        @if ($isTeamAdmin or $isInspector)
            <div class="flex flex-row sm:px-6 sm:py-4">
                <div>
                    <x-jet-danger-button wire:click="$emitTo('set-time-register-filters', 'open')"
                        class="w-auto h-8 ml-0 mb-2">
                        {{ __('Set filter') }}
                    </x-jet-danger-button>
                </div>
                <div>
                    <x-jet-button wire:click="unsetFilter" wire:loading.attr="disabled" class="w-auto h-8 ml-2 mb-2"
                        wire:model="isfiltered">
                        {{ __('Unset filters') }}
                    </x-jet-button>
                </div>
                <div>
                    <h1 class="w-auto ml-4 text-2xl text-red-600 mr-4 {{ $filtered ? 'visible' : 'invisible' }}">
                        {{ __('Filtered records') }}</h1>
                </div>
        @endif

        <!-- Livewire component to show time regisres -->
        <!-- Select no. of register to show -->
        <div class="mb-2 sm:flex items-center mx-auto sm:px-6 sm:py-0">
            <div class="w-full h-8 mb-2 mr-2 sm:flex sm:items-center sm:w-1/4">
                <span>Mostrar</span>
                <span>
                    <select wire:model='qtytoshow' class="mx-2 pt-1 h-8 form-control">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </span>
                <span class="visible sm:invisible lg:visible">{{ __('records') }}</span>
            </div>

            <!-- Show Add event button component -->
            <div class="w-auto mx-auto">
                <x-jet-danger-button class="whitespace-nowrap h-8 ml-0 sm:ml-2 mb-2 sm:mb-2 sm:my-0"
                    wire:click="$emitTo('add-event', 'add', '1')">
                        {{ __('Add event') }}
                </x-jet-danger-button>
            </div>
            @livewire('add-event')

            <!-- Show search bar -->
            <div class="w-auto mx-auto">
                    <x-jet-input class="w-full h-8 sm:mx-2 mb-2 pr-2" placeholder="{{ __('Search') }}" type="text"
                        wire:model="search" />
            </div>
            <div>
                <span class="w-auto ml-4 sm:whitespace-nowrap pt-2">
                    {{ __('Not confirmed') }}
                    <x-jet-checkbox class="h-6 w-6 ml-2 text-gray-600 checked:text-green-600" wire:model="confirmed"
                        wire:click="$set('filtered', false)" />
                </span>
            </div>
        </div>

        <!-- Instead of using method count() because of deferred loading of events-->
        @if (count($events))
            <!-- component -->
            <table class="block min-w-full border-collapse md:table">
                <thead class="block md:table-header-group">
                    <tr
                        class="absolute block border border-grey-500 md:border-none md:table-row -top-full md:top-auto -left-full md:left-auto md:relative ">

                        <th
                            class="block p-1 font-bold text-center text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell">
                            {{ __('Id') }}
                        </th>

                        {{-- TODO: This should be showed only in roles like admin or inspect --}}
                        @if ($isTeamAdmin or $isInspector)
                            <th class="block p-1 font-bold text-center text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell"
                                wire:click="order('name')">
                                {{ __('Worker') }}
                                <!-- Sort icon -->
                                @if ($sort == 'name')
                                    @if ($direction == 'asc')
                                        <i class="float-right mt-1 fa-solid fa-arrow-down-1-9"></i>
                                    @else
                                        <i class="float-right mt-1 fa-solid fa-arrow-down-9-1"></i>
                                    @endif
                                @else
                                    <i class='float-right mt-1 fa-solid fa-sort'></i>
                                @endif
                            </th>
                        @endif

                        <th class="block p-1 font-bold text-left text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell"
                            wire:click="order('start')">
                            {{ __('Start') }}
                            <!-- Sort icon -->
                            @if ($sort == 'start')
                                @if ($direction == 'asc')
                                    <i class="float-right mt-1 fa-solid fa-arrow-down-1-9"></i>
                                @else
                                    <i class="float-right mt-1 fa-solid fa-arrow-down-9-1"></i>
                                @endif
                            @else
                                <i class='float-right mt-1 fa-solid fa-sort'></i>
                            @endif
                        </th>

                        <th class="block p-1 font-bold text-left text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell"
                            wire:click="order('end')">
                            {{ __('End') }}
                            <!-- Sort icon -->
                            @if ($sort == 'end')
                                @if ($direction == 'asc')
                                    <i class="float-right mt-1 fa-solid fa-arrow-down-1-9"></i>
                                @else
                                    <i class="float-right mt-1 fa-solid fa-arrow-down-9-1"></i>
                                @endif
                            @else
                                <i class='float-right mt-1 fa-solid fa-sort'></i>
                            @endif
                        </th>
                        <th class="block p-1 font-bold text-left text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell"
                            wire:click="order('description')">
                            {{ __('Description') }}
                            <!-- Sort icon -->
                            @if ($sort == 'description')
                                @if ($direction == 'asc')
                                    <i class="float-right mt-1 fa-solid fa-arrow-down-a-z"></i>
                                @else
                                    <i class="float-right mt-1 fa-solid fa-arrow-down-z-a"></i>
                                @endif
                            @else
                                <i class='float-right mt-1 fa-solid fa-sort'></i>
                            @endif
                        </th>
                        <th
                            class="block p-1 w-min font-bold text-center text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell">
                            {{ __('Duration') }}
                        </th>
                        <th
                            class="block p-1 w-min font-bold text-center text-white bg-gray-600 cursor-pointer md:border md:border-grey-500 md:table-cell">
                            {{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody class="block md:table-row-group">
                    @foreach ($events as $ev)
                        <tr class="block bg-gray-300 border border-grey-500 md:border-none md:table-row">
                            <td class="block p-1 text-center md:border md:border-grey-500 md:table-cell"><span
                                    class="inline-block font-bold md:hidden">{{ __('Status') }}</span>
                                {{ $ev->id }}
                            </td>
                            @if ($isTeamAdmin or $isInspector)
                                <td class="block p-1 text-left md:border md:border-grey-500 md:table-cell"><span
                                        class="mr-2 inline-block font-bold md:hidden">{{ __('Worker') }}</span>{{ $ev->user_id . ' - ' . $ev->name . ' ' . $ev->family_name1 }}
                                </td>
                            @endif

                            <td class="block p-1 text-left md:border md:border-grey-500 md:table-cell"><span
                                    class="mr-2 inline-block font-bold md:hidden">{{ __('Start') }}</span>{{ $ev->start }}
                            </td>
                            <td class="block p-1 text-left md:border md:border-grey-500 md:table-cell"><span
                                    class="mr-2 inline-block font-bold md:hidden">{{ __('End') }}</span>{{ $ev->end }}
                            </td>
                            <td class="block p-1 text-left md:border md:border-grey-500 md:table-cell"><span
                                    class="mr-2 inline-block font-bold md:hidden">{{ __('Description') }}</span>{{ __($ev->description) }}
                            </td>
                            <td class="block p-1 text-left md:text-center md:border md:border-grey-500 md:table-cell">
                                <span
                                    class="mr-2 inline-block font-bold md:hidden">{{ __('Duration') }}</span>{{ $ev->getPeriod() }}
                            </td>
                            <td class="flex items-center justify-center p-1 md:border md:border-grey-500">
                                <div class="flex flex-row content-center float-right p-0 m-0 mx-min">
                                    <a class="btn {{ $ev['is_open'] ? 'btn-blue' : 'btn-gray' }}"
                                        wire:click="$emitTo('edit-event', 'edit', {{ $ev }})">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a class="btn {{ $ev['is_open'] ? 'btn-green' : 'btn-gray' }}"
                                        wire:click="$emit('confirmConfirmation', {{ $ev }})">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a class="btn {{ $ev['is_open'] ? 'btn-red' : 'btn-gray' }}"
                                        wire:click="$emit('confirmDeletion', {{ $ev }})">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- hasPages is included in this to avoid error while loading events-->
            @if ($events->hasPages())
                <div class="px-6 py-3">
                    {{ $events->links() }}
                </div>
            @endif
        @else
            <div class="px-4 shadow p-x6">{{ __('No records found already...') }}</div>
        @endif
    </div>

    @livewire('edit-event')

    <!-- Scripts to show SweetAlert modals -->
    @push('scripts')
        <!-- To throw notifications -->
        <script>
            //
            // Inespecific alert
            //
            Livewire.on('alert', function(message) {
                Swal.fire({
                    icon: 'success',
                    title: "{{ __('OK, perfect!') }}",
                    text: message,
                    timer: 1500,
                    footer: ''
                })
            })
            //
            // Fail alert 
            //
            Livewire.on('alertFail', function(message) {
                Swal.fire({
                    icon: 'info',
                    title: "{{ __('Ups!. Something happened. Chek your data!') }}",
                    text: message,
                    timer: 1500,
                    footer: ''
                })
            })

            //
            // Deletion confirmation alert 
            //
            Livewire.on('confirmDeletion', event => {
                if (event.is_open || {{ $isTeamAdmin ? 1 : 0 }}) {
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('You won\'t be able to undo this action!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, delete it!') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.emitTo('get-time-registers', 'remove', event);
                            Swal.fire({
                                icon: 'success',
                                title: "{{ __('Removed!') }}",
                                text: "{{ __('Event has been removed!') }}",
                                timer: 1500,
                                footer: ''
                            })
                        }
                    })
                    Livewire.emit('render');
                } else {
                    Livewire.emit('alertFail', "{{ __('Event is confirmed.') }}");
                }
            })

            //
            // Event confirmation alert 
            //
            Livewire.on('confirmConfirmation', event => {
                if ((event.is_open && event.end !== null)) {
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('You won\'t be able to undo this action!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, confirm it!') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.emitTo('get-time-registers', 'confirm', event);
                            Swal.fire({
                                icon: 'success',
                                title: "{{ __('Confirmed!') }}",
                                text: "{{ __('Event has been confirmed!') }}",
                                timer: 1500,
                                footer: ''
                            })
                        }
                    })
                    Livewire.emit('render');
                } else {
                    Livewire.emit('alertFail', 'Algo ha ido mal. Comprueba los datos');
                }
            })
        </script>
    @endpush
</div>
