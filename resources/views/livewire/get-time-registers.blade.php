<div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="px-6 py-4 flex items-center">
            <x-jet-input class="flex-1 mr-4" placeholder="Search" type="text" wire:model="search" />
            @livewire('add-event')
        </div>

        @if ($events->count())
            <!-- component -->
            <table class="min-w-full border-collapse block md:table">
                <thead class="block md:table-header-group">
                    <tr 
                        class="border border-grey-500 md:border-none block md:table-row absolute -top-full md:top-auto -left-full md:left-auto md:relative ">
                        <th class="cursor-pointer bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell"
                            wire:click="order('start_time')">
                            Start
                            <!-- Sort icon -->
                            @if ($sort == 'start_time')
                                @if ($direction == 'asc')
                                    <i class="fa-solid fa-arrow-down-1-9 float-right mt-1"></i>
                                @else
                                    <i class="fa-solid fa-arrow-down-9-1 float-right mt-1"></i>
                                @endif
                            @else
                                <i class='fa-solid fa-sort float-right mt-1'></i>
                            @endif
                        </th>
                        <th class="cursor-pointer bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell"
                            wire:click="order('end_time')">
                            End
                            <!-- Sort icon -->
                            @if ($sort == 'end_time')
                                @if ($direction == 'asc')
                                    <i class="fa-solid fa-arrow-down-1-9 float-right mt-1"></i>
                                @else
                                    <i class="fa-solid fa-arrow-down-9-1 float-right mt-1"></i>
                                @endif
                            @else
                                <i class='fa-solid fa-sort float-right mt-1'></i>
                            @endif
                        </th>
                        <th class="cursor-pointer bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell"
                            wire:click="order('description')">
                            Description
                            <!-- Sort icon -->
                            @if ($sort == 'description')
                                @if ($direction == 'asc')
                                    <i class="fa-solid fa-arrow-down-a-z float-right mt-1"></i>
                                @else
                                    <i class="fa-solid fa-arrow-down-z-a float-right mt-1"></i>
                                @endif
                            @else
                                <i class='fa-solid fa-sort float-right mt-1'></i>
                            @endif
                        </th>
                        <th
                            class="cursor-pointer bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell">
                            Actions</th>
                    </tr>
                </thead>

                <tbody class="block md:table-row-group">
                    @foreach ($events as $key => $event)
                        <tr class="bg-gray-300 border border-grey-500 md:border-none block md:table-row">
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">Start
                                    Time</span>{{ $event->start_time }}</td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">End
                                    Time</span>{{ $event->end_time }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">Description</span>{{ $event->description }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 block md:table-cell">
                                <span class="inline-block w-1/3 md:hidden font-bold">Actions</span>
                                <div class="flex flex-row max-w-fit mx-auto m-0 p-0 float-right">
                                    {{ $event->id }}
                                    <a class="btn btn-blue" wire:click="edit({{ $event }})">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a class="btn btn-green" wire:click="confirm({{ $event }})">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a class="btn btn-red" wire:click="remove({{ $event }})">
                                        <i class="fas fa-trash"></i>
                                    </a>    
                                    {{-- <ul>
                                    <li class="float-right">@livewire('edit-event', ['event' => $event], key($key))</li>                                   
                                    </ul> --}}
                                </div>                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-x6 px-4 shadow">No records found</div>
        @endif

    </div>

    <x-jet-dialog-modal wire:model="open_edit">

        <x-slot name='title'>
            Editar evento {{ $event->description }}
        </x-slot>

        <x-slot name='content'>
            <div class="mb-4">
                <x-jet-label value="Event End" />
                <x-jet-input wire:model="event.end_time" type="text" class="w-full" />                
            </div>

            <div>
                <x-jet-label value="Event description" />
                <x-jet-input wire:model="event.description" type="text" class="custom-textarea w-full"/>
            </div>
        </x-slot>

        <x-slot name='footer'>
            <x-jet-secondary-button wire:click="$set('open', false)">
                Cancel
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="save" wire:loading.attr="disabled" class="disabled:opacity-60">
                Update
            </x-jet-danger-button>
        </x-slot>

    </x-jet-dialog-modal>

</div>
