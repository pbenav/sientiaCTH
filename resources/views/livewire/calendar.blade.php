<div wire:key="calendar-{{ $refreshKey }}">
    @livewire('add-event')
    @livewire('edit-event')
    @livewire('events.event-details-modal') {{-- Unified modal --}}
    
    {{-- Adjustment Modal for MaxWorkdayDuration --}}
    @if($showAdjustmentModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="cancelAdjustment"></div>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" @click.stop>
                {{-- Modal Content --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-clock text-blue-600 dark:text-blue-400 mr-2"></i>
                        <p class="text-blue-800 dark:text-blue-200 font-medium">{{ __('Límite de jornada excedido') }}</p>
                    </div>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">
                        {{ __('Has superado el tiempo máximo permitido de jornada (:max min). Actualmente llevas :current min.', ['max' => $maxMinutes, 'current' => $currentMinutes]) }}
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-4 font-semibold">
                        {{ __('Elige una opción para ajustar tu fichaje:') }}
                    </p>
                    <div class="flex flex-col gap-3">
                        <button
                            wire:click="applyAdjustment('adjust_start')"
                            class="w-full flex justify-start items-center px-4 py-3 text-sm font-medium text-blue-800 dark:text-blue-200 bg-blue-100 dark:bg-blue-800/40 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-700 transition-all duration-200 border border-blue-300 dark:border-blue-700">
                            <i class="fas fa-step-forward mr-3 w-5"></i><span>1.- Ajustar hora de inicio (retrasar entrada)</span>
                        </button>
                        <button
                            wire:click="applyAdjustment('adjust_end')"
                            class="w-full flex justify-start items-center px-4 py-3 text-sm font-medium text-blue-800 dark:text-blue-200 bg-blue-100 dark:bg-blue-800/40 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-700 transition-all duration-200 border border-blue-300 dark:border-blue-700">
                            <i class="fas fa-step-backward mr-3 w-5"></i><span>2.- Ajustar hora de salida (adelantar salida)</span>
                        </button>
                        <button
                            wire:click="applyAdjustment('adjust_schedule')"
                            class="w-full flex justify-start items-center px-4 py-3 text-sm font-medium text-blue-800 dark:text-blue-200 bg-blue-100 dark:bg-blue-800/40 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-700 transition-all duration-200 border border-blue-300 dark:border-blue-700">
                            <i class="fas fa-calendar-alt mr-3 w-5"></i><span>3.- Ajustar al tramo horario (proporcional)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div id='calendar-container' wire:ignore style="height: 800px;">
        <div id='calendar'></div>
    </div>

    @push('scripts')
        <script src='{{ asset('js/fullcalendar-bundle.js') }}'></script>
        <script>
            document.addEventListener('livewire:load', function() {
                var calendarEl = document.getElementById('calendar');

                // Store calendar instance globally for refresh
                window.fullCalendarInstance = new FullCalendar.Calendar(calendarEl, {
                    plugins: [
                        FullCalendar.dayGridPlugin,
                        FullCalendar.timeGridPlugin,
                        FullCalendar.listPlugin,
                        FullCalendar.interactionPlugin
                    ],
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        day: 'Día',
                        list: 'Agenda'
                    },
                    allDayText: 'todo el día',
                    locale: 'es',
                    firstDay: {{ $weekStartsOn }}, // 0 = Sunday, 1 = Monday
                    initialView: 'timeGridWeek',
                    height: '100%',
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                    scrollTime: '{{ $scrollTime }}',
                    events: @json($this->getEvents()),
                    editable: true,
                    eventDurationEditable: true,
                    selectable: true,

                    

                    


                    // Callback for clicking an event
                    eventClick: function(info) {
                        console.log('[DEBUG] Event clicked:', {
                            id: info.event.id,
                            hasPrefix: info.event.id.startsWith('event_'),
                            extendedProps: info.event.extendedProps
                        });
                        
                        // Only allow editing user events, not holidays
                        if (info.event.id.startsWith('event_')) {
                            const eventId = parseInt(info.event.id.replace('event_', ''));
                            // Check if event is closed by looking at the icon
                            const iconHtml = info.event.extendedProps.iconHtml || '';
                            const isClosed = iconHtml.includes('fa-lock') && !iconHtml.includes('fa-lock-open');
                            
                            if (isClosed) {
                                // Show info modal for closed events using unified modal
                                Livewire.emit('showEventDetails', eventId);
                            } else {
                                // Allow editing open events
                                @this.call('triggerEditModal', eventId);
                            }
                        }
                    },

                    // Callback for clicking a date
                    dateClick: function(info) {
                        @this.call('triggerAddModal', {
                            origin: 'calendar',
                            date: info.dateStr
                        });
                    },

                    // Callback for dragging and dropping an event
                    eventDrop: function(info) {
                        // Only allow dropping user events, not holidays
                        if (!info.event.id.startsWith('event_')) {
                            info.revert();
                            return;
                        }
                        Swal.fire({
                            title: "{{ __('sweetalert.calendar.event_drop.title') }}",
                            text: "{{ __('sweetalert.calendar.event_drop.text') }}",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: "{{ __('sweetalert.calendar.event_drop.confirmButtonText') }}",
                            cancelButtonText: "{{ __('sweetalert.calendar.event_drop.cancelButtonText') }}"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const eventId = parseInt(info.event.id.replace('event_', ''));
                                @this.emit('eventDrop', eventId, info.event.start.toISOString(), info.event.end.toISOString());
                            } else {
                                info.revert();
                            }
                        });
                    },

                    // Callback for resizing an event
                    eventResize: function(info) {
                        // Only allow resizing user events, not holidays
                        if (!info.event.id.startsWith('event_')) {
                            info.revert();
                            return;
                        }
                        Swal.fire({
                            title: "{{ __('sweetalert.calendar.event_resize.title') }}",
                            text: "{{ __('sweetalert.calendar.event_resize.text') }}",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: "{{ __('sweetalert.calendar.event_resize.confirmButtonText') }}",
                            cancelButtonText: "{{ __('sweetalert.calendar.event_resize.cancelButtonText') }}"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const eventId = parseInt(info.event.id.replace('event_', ''));
                                @this.emit('eventResize', eventId, info.event.start.toISOString(), info.event.end.toISOString());
                            } else {
                                info.revert();
                            }
                        });
                    },

                    eventContent: function(info) {
                        // Accede a la propiedad personalizada 'iconHtml'
                        const iconHtml = info.event.extendedProps.iconHtml;
                        const title = info.event.title;

                        // Retorna un objeto con la propiedad 'html'
                        return {
                            html: `<span class="fc-title">${iconHtml} ${title}</span>`
                        };
                    }
                });

                window.fullCalendarInstance.render();
                
                // Inject CSS styles after render to override FullCalendar
                const style = document.createElement('style');
                style.textContent = `
                    /* Altura del contenedor */
                    #calendar-container {
                        height: 80vh;
                    }
                    
                    #calendar {
                        height: 100%;
                    }
                    
                    .fc {
                        height: 100% !important;
                    }
                    
                    .fc-scroller {
                        overflow-y: auto !important;
                        overflow-x: hidden !important;
                    }
                    
                    /* Responsive adjustments for mobile */
                    @media (max-width: 768px) {
                        #calendar-container {
                            height: 70vh;
                        }
                        
                        #calendar {
                            height: 100%;
                        }
                        
                        .fc .fc-toolbar-title {
                            font-size: 0.85rem !important;
                        }
                        
                        .fc .fc-button {
                            font-size: 0.7rem !important;
                            padding: 0.2rem 0.4rem !important;
                        }
                        
                        .fc .fc-col-header-cell-cushion {
                            font-size: 0.8rem !important;
                            padding: 0.2rem !important;
                        }
                        
                        .fc .fc-daygrid-day-number {
                            font-size: 0.8rem !important;
                            padding: 0.2rem !important;
                        }
                        
                        .fc .fc-event-title,
                        .fc .fc-event-time,
                        .fc .fc-title {
                            font-size: 0.85rem !important;
                            line-height: 1.2 !important;
                        }
                        
                        .fc .fc-event {
                            padding: 1px 2px !important;
                        }
                        
                        .fc .fc-timegrid-slot-label-cushion {
                            font-size: 0.75rem !important;
                            padding: 0 2px !important;
                        }
                        
                        .fc .fc-timegrid-axis-cushion {
                            font-size: 0.75rem !important;
                        }
                        
                        .fc .fc-timegrid-slot {
                            height: 2em !important;
                        }
                        
                        .fc .fc-toolbar {
                            padding: 0.5rem 0 !important;
                        }
                        
                        .fc .fc-toolbar-chunk {
                            display: flex;
                            align-items: center;
                            gap: 0.2rem;
                        }
                    }
                    
                    @media (max-width: 480px) {
                        .fc .fc-toolbar-title {
                            font-size: 0.75rem !important;
                        }
                        
                        .fc .fc-button {
                            font-size: 0.6rem !important;
                            padding: 0.15rem 0.3rem !important;
                        }
                        
                        .fc .fc-col-header-cell-cushion {
                            font-size: 0.7rem !important;
                            padding: 0.15rem !important;
                        }
                        
                        .fc .fc-daygrid-day-number {
                            font-size: 0.7rem !important;
                        }
                        
                        .fc .fc-event-title,
                        .fc .fc-event-time,
                        .fc .fc-title {
                            font-size: 0.75rem !important;
                            line-height: 1.1 !important;
                        }
                        
                        .fc .fc-timegrid-slot-label-cushion {
                            font-size: 0.7rem !important;
                        }
                        
                        .fc .fc-timegrid-slot {
                            height: 1.5em !important;
                        }
                        
                        .fc .fc-icon {
                            font-size: 0.65rem !important;
                        }
                    }
                `;
                document.head.appendChild(style);
            });
        </script>

        <script>
            // Save scroll position before reload
            window.addEventListener('beforeunload', () => {
                sessionStorage.setItem('calendarScrollPosition', window.scrollY);
            });

            // Restore scroll position after reload
            document.addEventListener('DOMContentLoaded', () => {
                const scrollPosition = sessionStorage.getItem('calendarScrollPosition');
                if (scrollPosition !== null) {
                    window.scrollTo(0, parseInt(scrollPosition));
                    sessionStorage.removeItem('calendarScrollPosition');
                }
            });

            // Listen for page reload event
            window.addEventListener('reload-page', () => {
                console.log('Reloading page to refresh calendar');
                // Save scroll position before reload
                sessionStorage.setItem('calendarScrollPosition', window.scrollY);
                // Force hard reload without cache
                window.location.href = window.location.href;
            });
        </script>

        <script>
            Livewire.on('modalClosed', () => {
                console.log('Modal closed from calendar view');
                // Add any additional logic if needed
            });
        </script>
    @endpush
</div>
