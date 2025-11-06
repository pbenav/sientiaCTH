<div>
    <!-- Event Info Modal -->
    @if($showModal && $eventData)
        <x-jet-dialog-modal wire:model="showModal">
            <x-slot name="title">
                <div class="flex items-center">
                    @if($eventData['is_open'])
                        <i class="fas fa-lock-open text-green-500 mr-2"></i>
                    @else
                        <i class="fas fa-lock text-red-500 mr-2"></i>
                    @endif
                    Detalles del Evento
                </div>
            </x-slot>

            <x-slot name="content">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-sm">
                    <!-- Left Column: Event Info -->
                    <div class="space-y-4">
                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">ID</label>
                            <p class="text-gray-800 font-mono">{{ $eventData['id'] }}</p>
                        </div>

                        @if(isset($eventData['event_type']))
                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Tipo de Evento</label>
                            <p class="text-gray-800 flex items-center">
                                <span class="inline-block w-3 h-3 rounded mr-2" style="background-color: {{ $eventData['event_type']['color'] ?? '#3788d8' }}"></span>
                                {{ $eventData['event_type']['name'] ?? 'N/A' }}
                            </p>
                        </div>
                        @endif

                        @if(isset($eventData['work_center']))
                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Centro de Trabajo</label>
                            <p class="text-gray-800">{{ $eventData['work_center']['name'] ?? 'N/A' }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Center Column: Time & Duration -->
                    <div class="space-y-4">
                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Inicio</label>
                            <p class="text-gray-800">
                                @if($eventData['start'])
                                    {{ \Carbon\Carbon::parse($eventData['start'])->format('d/m/Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Fin</label>
                            <p class="text-gray-800">
                                @if($eventData['end'])
                                    {{ \Carbon\Carbon::parse($eventData['end'])->format('d/m/Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Duración</label>
                            <p class="text-gray-800 font-mono">
                                @if($eventData['start'] && $eventData['end'])
                                    @php
                                        $start = \Carbon\Carbon::parse($eventData['start']);
                                        $end = \Carbon\Carbon::parse($eventData['end']);
                                        $duration = $start->diff($end);
                                        $hours = $duration->h + ($duration->days * 24);
                                        $minutes = $duration->i;
                                    @endphp
                                    {{ sprintf('%02d:%02d', $hours, $minutes) }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Right Column: Status & Metadata -->
                    <div class="space-y-4">
                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Estado</label>
                            <div class="mt-1">
                                @if($eventData['is_open'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-lock-open mr-1"></i>
                                        Abierto
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-lock mr-1"></i>
                                        Cerrado
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if(isset($eventData['is_authorized']))
                        <div>
                            <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Autorización</label>
                            <div class="mt-1">
                                @if($eventData['is_authorized'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Autorizado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pendiente
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="space-y-2">
                            @if(isset($eventData['created_at']))
                            <div>
                                <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Creado</label>
                                <p class="text-gray-800 text-xs">
                                    {{ \Carbon\Carbon::parse($eventData['created_at'])->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            @endif

                            @if(isset($eventData['updated_at']))
                            <div>
                                <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Actualizado</label>
                                <p class="text-gray-800 text-xs">
                                    {{ \Carbon\Carbon::parse($eventData['updated_at'])->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Description Section (full width) -->
                @if(isset($eventData['description']) && $eventData['description'])
                <div class="mt-6">
                    <label class="font-semibold text-gray-600 text-xs uppercase tracking-wide">Descripción</label>
                    <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                        <p class="text-gray-800">{{ $eventData['description'] }}</p>
                    </div>
                </div>
                @endif

                @if(!$eventData['is_open'])
                    <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Este evento está cerrado y no se puede modificar.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="closeModal" wire:loading.attr="disabled">
                    Cerrar
                </x-jet-secondary-button>
            </x-slot>
        </x-jet-dialog-modal>
    @endif
</div>