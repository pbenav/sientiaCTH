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
                        <th class="w-32 cursor-pointer bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell"
                            wire:click="order('userId')">
                            User ID
                            <!-- Sort icon -->
                            @if ($sort == 'userId')
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
                            wire:click="order('startTime')">
                            Start
                            <!-- Sort icon -->
                            @if ($sort == 'startTime')
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
                            wire:click="order('endTime')">
                            End
                            <!-- Sort icon -->
                            @if ($sort == 'endTime')
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

                    @foreach ($events as $event)
                        <tr class="bg-gray-300 border border-grey-500 md:border-none block md:table-row">
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">User</span>{{ $event->userId }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">Start
                                    time</span>{{ $event->startTime }}</td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">End
                                    Time</span>{{ $event->endTime }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">Description</span>{{ $event->description }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 block md:table-cell">
                                <span class="inline-block w-1/3 md:hidden font-bold">Actions</span>                                
                                @livewire('edit-event', ['event' => $event], key($event->id))                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-x6 px-4 shadow">No records found</div>
        @endif

    </div>

</div>
