<div>
    <!-- Event detail. Modal table -->
    <x-jet-dialog-modal wire:model="showModalEditEvent" maxWidth="2xl">

        <x-slot name='title'>
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-indigo-100 p-2 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Edit event') }} <span class="text-gray-400 font-normal">#{{ $event->id }}</span></h3>
                        <p class="text-sm text-gray-500">{{ $user->name }} {{ $user->family_name1 }}</p>
                    </div>
                </div>
                
                <div class="text-right">
                    @if($event->workCenter)
                        <div class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full inline-flex items-center mb-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $event->workCenter->name }}
                        </div>
                    @endif
                    
                    @if($event->eventType)
                        <div class="flex items-center justify-end mt-1">
                            @php
                                $eventColor = '#3788d8'; // Default color
                                if ($event->is_exceptional) {
                                    $eventColor = auth()->user()->currentTeam->special_event_color ?? '#DC2626';
                                } elseif ($event->eventType) {
                                    if ($event->eventType->color) {
                                        $eventColor = $event->eventType->color;
                                    } elseif (!$event->eventType->is_workday_type) {
                                        $eventColor = auth()->user()->currentTeam->special_event_color ?? '#EA8000';
                                    }
                                } else {
                                    $eventColor = auth()->user()->currentTeam->special_event_color ?? '#EA8000';
                                }
                            @endphp
                            <span class="inline-block w-3 h-3 rounded-full mr-2 shadow-sm" style="background-color: {{ $eventColor }}"></span>
                            <span class="text-sm font-medium text-gray-700">{{ $event->eventType->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </x-slot>

        <x-slot name='content'>
            <!-- Context Info Bar -->
            @if($workScheduleHint)
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md flex items-start">
                    <svg class="w-5 h-5 text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <span class="font-bold text-blue-800 block">{{ __('Work Schedule Hint') }}</span>
                        <span class="text-sm text-blue-700">{{ $workScheduleHint }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Form Grid -->
            <div class="grid grid-cols-1 gap-6">

                <!-- Left Column: Date & Time -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Fecha y hora</h4>
                        
                        @if (isset($event->eventType) && $event->eventType->is_all_day)
                            {{-- All-day event: show only date inputs --}}
                            <div class="space-y-4">
                                <div>
                                    <x-jet-label for="start_date" value="{{ __('Start date') }}" class="font-medium text-gray-700" />
                                    <input id="start_date" 
                                           type="date" 
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                           value="{{ $start_date }}"
                                           wire:change="$set('start_date', $event.target.value)"
                                           {{ $canBeModified ? '' : 'disabled' }} />
                                    <x-jet-input-error for="start_date" class="mt-1" />
                                </div>
                                <div>
                                    <x-jet-label for="end_date" value="{{ __('End date') }}" class="font-medium text-gray-700" />
                                    <input id="end_date" 
                                           type="date" 
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                                           value="{{ $end_date }}"
                                           wire:change="$set('end_date', $event.target.value)"
                                           {{ $canBeModified ? '' : 'disabled' }} />
                                    <x-jet-input-error for="end_date" class="mt-1" />
                                </div>
                            </div>
                        @else
                            {{-- Non-all-day event: compact date and time --}}
                            <div class="space-y-4">
                                <div>
                                    <x-jet-label value="{{ __('Start') }}" class="font-medium text-gray-700 mb-1" />
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <input type="date" 
                                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" 
                                                   value="{{ $start_date }}"
                                                   wire:change="$set('start_date', $event.target.value)"
                                                   {{ $canBeModified ? '' : 'disabled' }} />
                                            <x-jet-input-error for="start_date" class="mt-1" />
                                        </div>
                                        <div>
                                            <input type="time" 
                                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" 
                                                   value="{{ $start_time }}"
                                                   wire:change="$set('start_time', $event.target.value)"
                                                   {{ $canBeModified ? '' : 'disabled' }} 
                                                   step="300" />
                                            <x-jet-input-error for="start_time" class="mt-1" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <x-jet-label value="{{ __('End') }}" class="font-medium text-gray-700 mb-1" />
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <input type="date" 
                                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" 
                                                   value="{{ $end_date }}"
                                                   wire:change="$set('end_date', $event.target.value)"
                                                   {{ $canBeModified ? '' : 'disabled' }} />
                                            <x-jet-input-error for="end_date" class="mt-1" />
                                        </div>
                                        <div>
                                            <input type="time" 
                                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" 
                                                   value="{{ $end_time }}"
                                                   wire:change="$set('end_time', $event.target.value)"
                                                   {{ $canBeModified ? '' : 'disabled' }} 
                                                   step="300" />
                                            <x-jet-input-error for="end_time" class="mt-1" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Description & Observations -->
                <div class="space-y-6">
                    <div>
                        <x-jet-label for="event_description" value="{{ __('Description') }}" class="font-medium text-gray-700" />
                        <x-jet-input id="event_description" 
                                     class="mt-1 block w-full" 
                                     wire:model.defer="event.description"
                                     placeholder="{{ __('Add a description') }}"
                                     maxlength="255"
                                     {{ $canBeModified ? '' : 'disabled' }} />
                        <x-jet-input-error for='event.description' class="mt-1" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('If empty, event type name will be used') }}</p>
                    </div>

                    <div>
                        <x-jet-label for="event_observations" value="{{ __('Observations') }}" class="font-medium text-gray-700" />
                        <textarea id="event_observations"
                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm transition duration-150 ease-in-out sm:text-sm disabled:bg-gray-100 disabled:text-gray-500"
                                  wire:model.defer="event.observations"
                                  rows="4"
                                  placeholder="{{ __('event.observations.placeholder') }}"
                                  maxlength="255"
                                  {{ $canBeModified ? '' : 'disabled' }}></textarea>
                        <x-jet-input-error for='event.observations' class="mt-1" />
                    </div>
                </div>
            </div>

        </x-slot>

        <x-slot name='footer'>
            <div class="flex flex-col xs:flex-row xs:justify-between gap-3">
                <div class="flex flex-col xs:flex-row gap-3">
                    @if($canBeModified && $event->id)
                        <x-jet-danger-button wire:click="$emit('confirmDelete', {{ $event->id }})" class="justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            {{ __('Delete Event') }}
                        </x-jet-danger-button>
                    @endif
                </div>
                
                <div class="flex flex-col xs:flex-row gap-3">
                    <x-jet-secondary-button wire:click="$set('showModalEditEvent', false)" wire:target="GetTimeRegisters" class="justify-center">
                        {{ __('Cancel') }}
                    </x-jet-secondary-button>
    
                    @if($canBeModified)
                        <x-jet-button wire:click="update" wire:loading.attr="disabled"
                            class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 justify-center">
                            {{ __('Update event') }}
                        </x-jet-button>
                    @endif
                </div>
            </div>
        </x-slot>

    </x-jet-dialog-modal>
</div>

@push('scripts')
<script>
    function confirmDelete(eventId) {
        Swal.fire({
            title: "{{ __('sweetalert.edit_event.delete_confirmation.title') }}",
            text: "{{ __('sweetalert.edit_event.delete_confirmation.text') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: "{{ __('sweetalert.edit_event.delete_confirmation.confirmButtonText') }}",
            cancelButtonText: "{{ __('sweetalert.edit_event.delete_confirmation.cancelButtonText') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', eventId);
            }
        });
    }
</script>
@endpush
