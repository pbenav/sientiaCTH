<div>
    <!-- Event Edit Modal -->
    <x-jet-dialog-modal wire:model="showModalEditEvent" maxWidth="2xl">
        <x-slot name='title'>
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-100 p-2 rounded-full">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">
                            {{ __('Edit event') }} <span class="text-gray-400 font-normal text-sm">#{{ $event->id }}</span>
                        </h3>
                        <p class="text-xs text-gray-500">{{ $user->name }} {{ $user->family_name1 }}</p>
                    </div>
                </div>
                @if($event->is_exceptional)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ __('Exceptional') }}
                    </span>
                @endif
            </div>
        </x-slot>

        <x-slot name='content'>
            <!-- Work Schedule Hint -->
            @if($workScheduleHint)
                <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-md flex items-start">
                    <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm">
                        <span class="font-bold text-blue-800 block">{{ __('Work Schedule Hint') }}</span>
                        <span class="text-blue-700">{{ $workScheduleHint }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Form Grid (2 columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Left Column: Date & Time -->
                <div class="space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">{{ __('Date and Time') }}</h4>
                        
                        @if (isset($event->eventType) && $event->eventType->is_all_day)
                            <!-- All-day event -->
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-600 block mb-1">{{ __('Start date') }}</label>
                                    <input type="date" 
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" 
                                           value="{{ $start_date }}"
                                           wire:change="$set('start_date', $event.target.value)"
                                           {{ $canBeModified ? '' : 'disabled' }} />
                                    <x-jet-input-error for="start_date" class="mt-1" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600 block mb-1">{{ __('End date') }}</label>
                                    <input type="date" 
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" 
                                           value="{{ $end_date }}"
                                           wire:change="$set('end_date', $event.target.value)"
                                           {{ $canBeModified ? '' : 'disabled' }} />
                                    <x-jet-input-error for="end_date" class="mt-1" />
                                </div>
                            </div>
                        @else
                            <!-- Regular event with time -->
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-600 block mb-1">{{ __('Start') }}</label>
                                    <div class="flex gap-2">
                                        <input type="date" 
                                               class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" 
                                               value="{{ $start_date }}"
                                               wire:change="$set('start_date', $event.target.value)"
                                               {{ $canBeModified ? '' : 'disabled' }} />
                                        <input type="time" 
                                               class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" 
                                               value="{{ $start_time }}"
                                               wire:change="$set('start_time', $event.target.value)"
                                               {{ $canBeModified ? '' : 'disabled' }}
                                               step="300" />
                                    </div>
                                    <x-jet-input-error for="start_date" class="mt-1" />
                                    <x-jet-input-error for="start_time" class="mt-1" />
                                </div>

                                <div>
                                    <label class="text-xs text-gray-600 block mb-1">{{ __('End') }}</label>
                                    <div class="flex gap-2">
                                        <input type="date" 
                                               class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" 
                                               value="{{ $end_date }}"
                                               wire:change="$set('end_date', $event.target.value)"
                                               {{ $canBeModified ? '' : 'disabled' }} />
                                        <input type="time" 
                                               class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" 
                                               value="{{ $end_time }}"
                                               wire:change="$set('end_time', $event.target.value)"
                                               {{ $canBeModified ? '' : 'disabled' }}
                                               step="300" />
                                    </div>
                                    <x-jet-input-error for="end_date" class="mt-1" />
                                    <x-jet-input-error for="end_time" class="mt-1" />
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Description & Observations -->
                <div class="space-y-4">
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">{{ __('Description') }}</label>
                        <x-jet-input class="block w-full text-sm" 
                                     wire:model.defer="event.description"
                                     placeholder="{{ __('Add a description') }}"
                                     maxlength="255"
                                     {{ $canBeModified ? '' : 'disabled' }} />
                        <x-jet-input-error for='event.description' class="mt-1" />
                        <p class="mt-1 text-xs text-gray-400">{{ __('If empty, event type name will be used') }}</p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-600 block mb-1">{{ __('Observations') }}</label>
                        <textarea class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm"
                                  wire:model.defer="event.observations"
                                  rows="4"
                                  placeholder="{{ __('event.observations.placeholder') }}"
                                  maxlength="255"
                                  {{ $canBeModified ? '' : 'disabled' }}></textarea>
                        <x-jet-input-error for='event.observations' class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Validation - Inline Icons -->
            <div class="flex items-center gap-4 text-xs pt-4 border-t border-gray-200 mt-4">
                <span class="font-semibold text-gray-600 uppercase tracking-wide">{{ __('Validation') }}:</span>
                
                <!-- GPS -->
                @if($event->latitude && $event->longitude)
                    <a href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}" 
                       target="_blank" 
                       class="flex items-center gap-1 text-green-600 hover:text-green-700 transition-colors"
                       title="GPS: {{ number_format($event->latitude, 4) }}, {{ number_format($event->longitude, 4) }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium">GPS</span>
                    </a>
                @else
                    <span class="flex items-center gap-1 text-gray-300" title="{{ __('GPS not available') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span>GPS</span>
                    </span>
                @endif
                
                <!-- NFC -->
                <span class="flex items-center gap-1 {{ $event->nfc_tag_id ? 'text-blue-600' : 'text-gray-300' }}"
                      title="{{ $event->nfc_tag_id ? 'NFC: ' . $event->nfc_tag_id : __('NFC not available') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    <span class="{{ $event->nfc_tag_id ? 'font-medium' : '' }}">NFC</span>
                </span>
                
                <!-- IP -->
                <span class="flex items-center gap-1 {{ $event->ip_address ? 'text-purple-600' : 'text-gray-300' }}"
                      title="{{ $event->ip_address ? 'IP: ' . $event->ip_address : __('IP not available') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                    <span class="{{ $event->ip_address ? 'font-medium font-mono' : '' }}">{{ $event->ip_address ?? 'IP' }}</span>
                </span>
            </div>
        </x-slot>

        <x-slot name='footer'>
            <div class="flex items-center justify-between w-full">
                <div>
                    @if($canBeModified && $event->id)
                        <x-jet-danger-button onclick="confirmDelete({{ $event->id }})">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            {{ __('Delete Event') }}
                        </x-jet-danger-button>
                    @endif
                </div>
                
                <div class="flex gap-3">
                    <x-jet-secondary-button wire:click="$set('showModalEditEvent', false)" wire:target="GetTimeRegisters">
                        {{ __('Cancel') }}
                    </x-jet-secondary-button>
    
                    @if($canBeModified)
                        <x-jet-button wire:click="update" wire:loading.attr="disabled"
                            class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500">
                            {{ __('Update event') }}
                        </x-jet-button>
                    @endif
                </div>
            </div>
        </x-slot>
    </x-jet-dialog-modal>

    <!-- Adjustment Modal -->
    <x-jet-dialog-modal wire:model="showAdjustmentModal" maxWidth="md">
        <x-slot name="title">
            <div class="flex items-center text-blue-600">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Límite de jornada excedido') }}
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    {{ __('Has superado el tiempo máximo permitido de jornada (:max min). Actualmente el evento dura :current min.', ['max' => $maxMinutes, 'current' => $currentMinutes]) }}
                </p>
                <p class="text-sm font-semibold text-gray-700 mt-2">
                    {{ __('Elige una opción para ajustar el evento:') }}
                </p>
            </div>

            <div class="space-y-3">
                <button wire:click="applyAdjustment('adjust_start')" class="w-full flex items-center p-3 text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-blue-200 rounded-full mr-3 text-blue-700 font-bold">1</span>
                    <span class="text-left">
                        <span class="block font-bold">{{ __('Ajustar hora de inicio') }}</span>
                        <span class="block text-xs font-normal text-blue-600">{{ __('Retrasar la entrada para cumplir el límite') }}</span>
                    </span>
                </button>

                <button wire:click="applyAdjustment('adjust_end')" class="w-full flex items-center p-3 text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-blue-200 rounded-full mr-3 text-blue-700 font-bold">2</span>
                    <span class="text-left">
                        <span class="block font-bold">{{ __('Ajustar hora de salida') }}</span>
                        <span class="block text-xs font-normal text-blue-600">{{ __('Adelantar la salida para cumplir el límite') }}</span>
                    </span>
                </button>

                <button wire:click="applyAdjustment('adjust_schedule')" class="w-full flex items-center p-3 text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-blue-200 rounded-full mr-3 text-blue-700 font-bold">3</span>
                    <span class="text-left">
                        <span class="block font-bold">{{ __('Ajustar al tramo horario') }}</span>
                        <span class="block text-xs font-normal text-blue-600">{{ __('Ajustar proporcionalmente al horario laboral') }}</span>
                    </span>
                </button>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showAdjustmentModal', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>
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
