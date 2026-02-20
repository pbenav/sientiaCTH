<div>
    <x-jet-dialog-modal wire:model="showAddEventModal" maxWidth="2xl">

        <x-slot name="title">
            <div class="flex items-center space-x-3">
                <div class="bg-indigo-100 p-2 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('Add new event') }}</h3>
            </div>
        </x-slot>

        <x-slot name="content">
            <!-- Info Alert -->
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                @if ((Auth::user() && Auth::user()->meta) || $workScheduleHint)
                    <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        @php
                            $defaultWorkCenter = null;
                            if (Auth::user() && Auth::user()->meta) {
                                $defaultWorkCenterMeta = Auth::user()
                                    ->meta->where(
                                        'meta_key',
                                        'default_work_center_id_team_' . Auth::user()->currentTeam->id,
                                    )
                                    ->first();
                                if ($defaultWorkCenterMeta && Auth::user()->currentTeam) {
                                    $defaultWorkCenter = Auth::user()
                                        ->currentTeam->workCenters()
                                        ->find($defaultWorkCenterMeta->meta_value);
                                }
                            }
                        @endphp

                        @if ($defaultWorkCenter)
                            <div class="flex items-center text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="font-semibold mr-2">{{ __('Default Work Center') }}:</span>
                                <span>{{ $defaultWorkCenter->name }}</span>
                            </div>
                        @endif

                        @if ($workScheduleHint)
                            <div class="flex items-center text-sm text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold mr-2">{{ __('Work Schedule Hint') }}:</span>
                                <span>{{ $workScheduleHint }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            @endauth

            <!-- Event Type Selection (Full Width) -->
            <div class="mb-6">
                <x-jet-label for="event_type_id" value="{{ __('Event Type') }}"
                    class="required font-medium text-gray-700" />
                <select id="event_type_id"
                    class="mt-1 block w-full border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md shadow-sm"
                    required wire:model.live="event_type_id" name="event_type_id">
                    <option value="">{{ __('Select an option') }}</option>
                    @foreach ($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='event_type_id' class="mt-1" />
            </div>

            <!-- Main Form Grid (2 columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Column: Date & Time -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Fecha y hora</h4>

                        @if ($selectedEventType && $selectedEventType->is_all_day)
                            <!-- All-day event: Date Range -->
                            <div class="space-y-4">
                                <div>
                                    <x-jet-label for="start_date" value="{{ __('Start date') }}"
                                        class="font-medium text-gray-700" />
                                    <x-jet-input id="start_date" type="date" wire:model.defer="start_date"
                                        class="mt-1 block w-full" required />
                                    <x-jet-input-error for="start_date" class="mt-1" />
                                </div>
                                <div>
                                    <x-jet-label for="end_date" value="{{ __('End date') }}"
                                        class="font-medium text-gray-700" />
                                    <x-jet-input id="end_date" type="date" wire:model.defer="end_date"
                                        class="mt-1 block w-full" required />
                                    <x-jet-input-error for="end_date" class="mt-1" />
                                </div>
                            </div>
                        @else
                            <!-- Regular event: Date and Time (only start) -->
                            <div>
                                <x-jet-label value="{{ __('Start') }}" class="font-medium text-gray-700 mb-1" />
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <x-jet-input type="date" wire:model.defer="start_date"
                                            class="block w-full text-sm" required />
                                        <x-jet-input-error for="start_date" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-jet-input type="time" wire:model.defer="start_time"
                                            class="block w-full text-sm" required step="300" />
                                        <x-jet-input-error for="start_time" class="mt-1" />
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Description & Observations -->
                <div class="space-y-6">
                    <div>
                        <x-jet-label for="description" value="{{ __('Description') }}"
                            class="font-medium text-gray-700" />
                        <x-jet-input id="description" class="mt-1 block w-full" wire:model.defer="description"
                            placeholder="{{ __('Add a description') }}" maxlength="255" />
                        <x-jet-input-error for='description' class="mt-1" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('If empty, event type name will be used') }}</p>
                    </div>

                    <div>
                        <x-jet-label for="observations" value="{{ __('Observations') }}"
                            class="font-medium text-gray-700" />
                        <textarea id="observations"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm"
                            wire:model.defer="observations" rows="4" placeholder="{{ __('event.observations.placeholder') }}"
                            maxlength="255"></textarea>
                        <x-jet-input-error for='observations' class="mt-1" />
                    </div>
                </div>
            </div>

        </x-slot>

        <x-slot name="footer">
            <div class="flex flex-col xs:flex-row xs:justify-end gap-3">
                <x-jet-secondary-button wire:click="$set('showAddEventModal', false)" wire:loading.attr="disabled"
                    class="justify-center">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>

                <x-jet-button id="save-event-btn" wire:loading.attr="disabled"
                    class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 justify-center">
                    {{ __('Guardar evento') }}
                </x-jet-button>
            </div>
        </x-slot>

    </x-jet-dialog-modal>

    <!-- Adjustment Modal -->
    <x-jet-dialog-modal wire:model="showAdjustmentModal" maxWidth="md">
        <x-slot name="title">
            <div class="flex items-center text-blue-600">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                <button wire:click="applyAdjustment('adjust_start')"
                    class="w-full flex items-center p-3 text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <span
                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-blue-200 rounded-full mr-3 text-blue-700 font-bold">1</span>
                    <span class="text-left">
                        <span class="block font-bold">{{ __('Ajustar hora de inicio') }}</span>
                        <span
                            class="block text-xs font-normal text-blue-600">{{ __('Retrasar la entrada para cumplir el límite') }}</span>
                    </span>
                </button>

                <button wire:click="applyAdjustment('adjust_end')"
                    class="w-full flex items-center p-3 text-sm font-medium text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    <span
                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-blue-200 rounded-full mr-3 text-blue-700 font-bold">2</span>
                    <span class="text-left">
                        <span class="block font-bold">{{ __('Ajustar hora de salida') }}</span>
                        <span
                            class="block text-xs font-normal text-blue-600">{{ __('Adelantar la salida para cumplir el límite') }}</span>
                    </span>
                </button>

                @php
                    $isPastOrToday =
                        \Carbon\Carbon::parse($start_date)->isPast() || \Carbon\Carbon::parse($start_date)->isToday();
                @endphp
                <button wire:click="applyAdjustment('adjust_schedule')"
                    class="w-full flex items-center p-3 text-sm font-medium {{ $isPastOrToday ? 'text-orange-800 bg-orange-50 hover:bg-orange-100 border-orange-200' : 'text-blue-800 bg-blue-50 hover:bg-blue-100 border-blue-200' }} rounded-lg border transition-colors">
                    <span
                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center {{ $isPastOrToday ? 'bg-orange-200 text-orange-700' : 'bg-blue-200 text-blue-700' }} rounded-full mr-3 font-bold">3</span>
                    <span class="text-left">
                        @if ($isPastOrToday)
                            <span class="block font-bold">{{ __('Registrar como excepcional') }}</span>
                            <span
                                class="block text-xs font-normal {{ $isPastOrToday ? 'text-orange-600' : 'text-blue-600' }}">{{ __('Guardar con la duración real y marcar para revisión del administrador') }}</span>
                        @else
                            <span class="block font-bold">{{ __('Ajustar al tramo horario') }}</span>
                            <span
                                class="block text-xs font-normal text-blue-600">{{ __('Ajustar proporcionalmente al horario laboral') }}</span>
                        @endif
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
        // Log GPS status when modal opens
        window.addEventListener('show-add-event-modal', function() {
            const userHasGeoEnabled =
                {{ auth()->check() && auth()->user()->geolocation_enabled ? 'true' : 'false' }};

            if (!userHasGeoEnabled) {
                console.log('[GPS] Disabled for this user');
                return;
            }

            if (typeof window.cachedGeoPosition !== 'undefined' && window.cachedGeoPosition) {
                console.log('[GPS] GPS ready for save():', window.cachedGeoPosition.latitude, window
                    .cachedGeoPosition.longitude);
            } else {
                console.log('[GPS] No GPS position available');
            }
        });

        // Handle save button click with GPS coordinates
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'save-event-btn') {
                e.preventDefault();
                e.stopPropagation();

                console.log('[GPS] Save button clicked');

                // Get the Livewire component
                const component = Livewire.find(e.target.closest('[wire\\:id]').getAttribute('wire:id'));

                if (component) {
                    // Check if GPS is available
                    if (window.cachedGeoPosition) {
                        console.log('[GPS] Calling save() with GPS:', window.cachedGeoPosition.latitude, window
                            .cachedGeoPosition.longitude);
                        component.call('save', window.cachedGeoPosition.latitude, window.cachedGeoPosition
                            .longitude);
                    } else {
                        console.log('[GPS] Calling save() without GPS');
                        component.call('save', null, null);
                    }
                } else {
                    console.error('[GPS] Livewire component not found');
                }
            }
        });
    </script>
@endpush
