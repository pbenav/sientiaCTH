<div>
    <x-jet-dialog-modal wire:model="showAddEventModal" maxWidth="2xl">

        <x-slot name="title">
            <div class="flex items-center space-x-3">
                <div class="bg-indigo-100 p-2 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('Add new event') }}</h3>
            </div>
        </x-slot>

        <x-slot name="content">
            <!-- Info Alert -->
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-blue-700">
                            La <strong>función del registro</strong> horario es demostrar la hora de entrada y salida.
                            Por favor, acostúmbrate a hacerlo a la hora correcta.
                        </p>
                        <p class="mt-2 text-sm text-blue-700 font-medium">
                            ¡Elige bien el tipo de evento! No podrá ser modificado una vez creado.
                        </p>
                    </div>
                </div>
            </div>

            @auth
                @if((Auth::user() && Auth::user()->meta) || $workScheduleHint)
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        @php
                            $defaultWorkCenter = null;
                            if (Auth::user() && Auth::user()->meta) {
                                $defaultWorkCenterMeta = Auth::user()->meta->where('meta_key', 'default_work_center_id')->first();
                                if ($defaultWorkCenterMeta && Auth::user()->currentTeam) {
                                    $defaultWorkCenter = Auth::user()->currentTeam->workCenters()->find($defaultWorkCenterMeta->meta_value);
                                }
                            }
                        @endphp

                        @if($defaultWorkCenter)
                            <div class="flex items-center text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="font-semibold mr-2">{{ __('Default Work Center') }}:</span>
                                <span>{{ $defaultWorkCenter->name }}</span>
                            </div>
                        @endif

                        @if($workScheduleHint)
                            <div class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold mr-2">{{ __('Work Schedule Hint') }}:</span>
                                <span>{{ $workScheduleHint }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            @endauth

            <div class="space-y-6">
                <!-- Event Type -->
                <div>
                    <x-jet-label for="event_type_id" value="{{ __('Event Type') }}" class="required font-medium text-gray-700" />
                    <select id="event_type_id" 
                            class="mt-1 block w-full border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm" 
                            required 
                            wire:model.live="event_type_id" 
                            name="event_type_id">
                        <option value="">{{ __('Select an option') }}</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for='event_type_id' class="mt-1" />
                </div>

                @if($selectedEventType && $selectedEventType->is_all_day)
                    <!-- All-day event: Date Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-jet-label for="start_date" value="{{ __('Start date') }}" class="font-medium text-gray-700" />
                            <x-jet-input id="start_date" type="date" wire:model.defer="start_date" class="mt-1 block w-full" required />
                            <x-jet-input-error for="start_date" class="mt-1" />
                        </div>
                        <div>
                            <x-jet-label for="end_date" value="{{ __('End date') }}" class="font-medium text-gray-700" />
                            <x-jet-input id="end_date" type="date" wire:model.defer="end_date" class="mt-1 block w-full" required />
                            <x-jet-input-error for="end_date" class="mt-1" />
                        </div>
                    </div>
                @else
                    <!-- Regular event: Date and Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-jet-label value="{{ __('Start') }}" class="font-medium text-gray-700" />
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <x-jet-input type="date" wire:model.defer="start_date" class="block w-full text-sm" required />
                                <x-jet-input type="time" wire:model.defer="start_time" class="block w-full text-sm" required step="300" />
                            </div>
                            <x-jet-input-error for="start_date" class="mt-1" />
                            <x-jet-input-error for="start_time" class="mt-1" />
                        </div>

                        <div>
                            <x-jet-label value="{{ __('End') }}" class="font-medium text-gray-700" />
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <x-jet-input type="date" wire:model.defer="end_date" class="block w-full text-sm" required />
                                <x-jet-input type="time" wire:model.defer="end_time" class="block w-full text-sm" required step="300" />
                            </div>
                            <x-jet-input-error for="end_date" class="mt-1" />
                            <x-jet-input-error for="end_time" class="mt-1" />
                        </div>
                    </div>
                @endif

                <!-- Description -->
                <div>
                    <x-jet-label for="description" value="{{ __('Description') }}" class="font-medium text-gray-700" />
                    <x-jet-input id="description" 
                                 class="mt-1 block w-full" 
                                 wire:model.defer="description"
                                 placeholder="{{ __('Add a description') }}"
                                 maxlength="255" />
                    <x-jet-input-error for='description' class="mt-1" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('If empty, event type name will be used') }}</p>
                </div>

                <!-- Observations -->
                <div>
                    <x-jet-label for="observations" value="{{ __('Observations') }}" class="font-medium text-gray-700" />
                    <textarea id="observations"
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm"
                              wire:model.defer="observations"
                              rows="3"
                              placeholder="{{ __('event.observations.placeholder') }}"
                              maxlength="255"></textarea>
                    <x-jet-input-error for='observations' class="mt-1" />
                </div>
            </div>

        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-jet-secondary-button wire:click="$set('showAddEventModal', false)" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>

                <x-jet-button wire:click="save" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500">
                    {{ __('Save event') }}
                </x-jet-button>
            </div>
        </x-slot>

    </x-jet-dialog-modal>
</div>
