<div>
    <x-jet-dialog-modal wire:model="showAddEventModal" maxWidth="2xl">

        <x-slot name="title">
            <div class="flex items-center space-x-3 text-gray-800">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-bold text-lg">{{ __('Add new event') }}</span>
            </div>
        </x-slot>

        <x-slot name="content">
            <!-- Info Alert -->
            <div class="mb-6 bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-indigo-700">
                            La <strong>función del registro</strong> horario es demostrar la hora de entrada y salida.
                            Por favor, acostúmbrate a hacerlo a la hora correcta.
                        </p>
                        <p class="mt-2 text-sm text-indigo-700 font-medium">
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
                            <div class="flex items-center text-sm text-gray-600 mb-1">
                                <span class="font-semibold text-gray-700 mr-2">{{ __('Default Work Center') }}:</span>
                                <span>{{ $defaultWorkCenter->name }}</span>
                            </div>
                        @endif

                        @if($workScheduleHint)
                            <div class="flex items-center text-sm text-gray-600">
                                <span class="font-semibold text-gray-700 mr-2">{{ __('Work Schedule Hint') }}:</span>
                                <span>{{ $workScheduleHint }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            @endauth

            <div class="grid grid-cols-1 gap-6">
                <!-- Event Type -->
                <div>
                    <x-jet-label for="event_type_id" value="{{ __('Event Type') }}" class="required font-semibold text-gray-700" />
                    <div class="mt-1 relative">
                        <select id="event_type_id" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm transition duration-150 ease-in-out" 
                                required 
                                wire:model.live="event_type_id" 
                                name="event_type_id">
                            <option value="">{{ __('Select an option') }}</option>
                            @foreach($eventTypes as $eventType)
                                <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-jet-input-error for='event_type_id' class="mt-1" />
                </div>

                <!-- Dates -->
                @if ($selectedEventType && $selectedEventType->is_all_day)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-jet-label for="start_date" value="{{ __('Start date') }}" class="required font-semibold text-gray-700" />
                            <x-jet-input id="start_date" type="date" class="mt-1 block w-full" wire:model.defer="start_date" />
                            <x-jet-input-error for="start_date" class="mt-1" />
                        </div>
                        <div>
                            <x-jet-label for="end_date" value="{{ __('End date') }}" class="required font-semibold text-gray-700" />
                            <x-jet-input id="end_date" type="date" class="mt-1 block w-full" wire:model.defer="end_date" />
                            <x-jet-input-error for="end_date" class="mt-1" />
                        </div>
                    </div>
                @else
                    <div>
                        <x-jet-label value="{{ __('Start date and time') }}" class="required font-semibold text-gray-700" />
                        <div class="mt-1 grid grid-cols-2 gap-4">
                            <div>
                                <x-jet-input type="date" class="block w-full" wire:model.defer="start_date" />
                                <x-jet-input-error for="start_date" class="mt-1" />
                            </div>
                            <div>
                                <x-jet-input type="time" class="block w-full" wire:model.defer="start_time" />
                                <x-jet-input-error for="start_time" class="mt-1" />
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Description -->
                <div>
                    <x-jet-label for="description" value="{{ __('Description') }}" class="font-semibold text-gray-700" />
                    <x-jet-input id="description" 
                                 class="mt-1 block w-full" 
                                 wire:model="description"
                                 placeholder="{{ __('Add a description') }}"
                                 maxlength="255" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('If empty, event type name will be used') }}</p>
                    <x-jet-input-error for='description' class="mt-1" />
                </div>

                <!-- Observations -->
                <div>
                    <x-jet-label for="observations" value="{{ __('Observations') }}" class="font-semibold text-gray-700" />
                    <textarea id="observations"
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm transition duration-150 ease-in-out sm:text-sm"                
                              wire:model="observations"
                              rows="3"
                              placeholder="{{ __('Add any additional observations here...') }}"
                              name="observations"
                              maxlength="255"></textarea>
                    <x-jet-input-error for='observations' class="mt-1" />
                </div>
            </div>

            <div class="hidden">
                <input type="hidden" id="user_id" name="user_id" wire:model.defer="user_id">
            </div>

        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-jet-secondary-button wire:click="cancel" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>

                <x-jet-button wire:click="save('')" 
                              wire:loading.attr="disabled" 
                              class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500"
                              wire_target="save">
                    <span wire:loading.remove wire:target="save">{{ __('Create Event') }}</span>
                    <span wire:loading wire:target="save" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Creating...') }}
                    </span>
                </x-jet-button>
            </div>
        </x-slot>

    </x-jet-dialog-modal>
</div>
