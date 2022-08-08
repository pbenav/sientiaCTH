<div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Event list. Main table -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">      

        <div class="flex px-6 py-4 items-center">
            @livewire('add-event')
            <x-jet-danger-button class="flex-1 w-3/4" wire:click="$emitTo('add-event', 'add', open=true)">
                {{ __('Add event') }}
            </x-jet-danger-button>
            <x-jet-input class="flex-1 ml-4 w-1/4" placeholder="Search" type="text" wire:model="search" />
        
        </div>

        @if ($events->count())
            <!-- component -->
            <table class="min-w-full border-collapse block md:table">
                <thead class="block md:table-header-group">
                    <tr
                        class="border border-grey-500 md:border-none block md:table-row absolute -top-full md:top-auto -left-full md:left-auto md:relative ">
                        <th class="cursor-pointer bg-gray-600 p-2 text-white font-bold md:border md:border-grey-500 text-left block md:table-cell"
                            wire:click="order('start')">
                            {{ __('Start') }}
                            <!-- Sort icon -->
                            @if ($sort == 'start')
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
                            wire:click="order('end')">
                            {{ __('End') }}
                            <!-- Sort icon -->
                            @if ($sort == 'end')
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
                            {{ __('Description') }}
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
                            {{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody class="block md:table-row-group">
                    @foreach ($events as $ev)
                        <tr class="bg-gray-300 border border-grey-500 md:border-none block md:table-row">
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">{{ __('Start') }}</span>{{ $ev->start }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">{{ __('End') }}</span>{{ $ev->end }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 text-left block md:table-cell"><span
                                    class="inline-block w-1/3 md:hidden font-bold">{{ __('Description') }}</span>{{ $ev->description }}
                            </td>
                            <td class="p-2 md:border md:border-grey-500 block md:table-cell">
                                <span class="inline-block w-1/3 md:hidden font-bold">{{ __('Actions') }}</span>
                                <div class="flex flex-row max-w-fit mx-auto m-0 p-0 float-right">
                                    <a class="btn btn-blue" wire:click="edit({{ $ev }})">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a class="btn btn-green" wire:click="$emit('confirmConfirmation', {{ $ev }})">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a class="btn btn-red" wire:click="$emit('confirmDeletion', {{ $ev }})">
                                        <i class="fas fa-trash"></i>
                                    </a>
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

    <div>
        <!-- Event detail. Modal table -->
        <x-jet-dialog-modal wire:model="open_edit">
    
            <x-slot name='title'>
                {{ __('Edit event') }}: <span wire:model.defer='event.id'></span>
            </x-slot>
    
            <x-slot name='content'>
    
                <div class="mb-4">
                    {{-- datepicker --}}
                    <x-datepicker label="Start date" wire:model="event.start">
                    </x-datepicker>
                    <x-jet-input-error for='event.start' />
                    <x-datepicker label="End date" wire:model="event.end">
                    </x-datepicker>
                    <x-jet-input-error for='event.end' />
                </div>
                {{-- end-datepicker --}}
    
                <div>
                    <x-jet-label value="{{ __('Description') }}" />
                    <select class="custom-textarea w-full" wire:model.defer="event.description" name="event.description"
                        class="mt-2 text-sm sm:text-base pl-2 pr-4 rounded-lg border border-gray-400 w-full py-2 focus:outline-none focus:border-blue-400"
                        required>
                        {{-- TODO Integrate causes as new model --}}
                        <option value="{{ __('Choose a description') }}">{{ __('Choose a description') }}</option>
                        <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                        <option value="{{ __('Lunch') }}">{{ __('Lunch') }}</option>
                        <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                    </select>
                    <x-jet-input-error for='event.description' />
                </div>
            </x-slot>
    
            <x-slot name='footer'>
                <x-jet-secondary-button wire:click="$set('open_edit', false)">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>
    
                <x-jet-danger-button wire:click="update" wire:loading.attr="disabled" class="disabled:bg-blue-500 ml-2"
                    wire_target="update">
                    {{ __('Update event') }}
                </x-jet-danger-button>
            </x-slot>
    
        </x-jet-dialog-modal>
    </div>       

</div>
