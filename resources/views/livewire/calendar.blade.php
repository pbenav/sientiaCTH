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
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                {{ __('Duración Máxima Excedida') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Has superado el tiempo máximo permitido de jornada (:max min). Actualmente llevas :current min.', [
                                        'max' => $maxMinutes,
                                        'current' => $currentMinutes
                                    ]) }}
                                </p>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-4">
                                    {{ __('Elige una opción para ajustar:') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-col sm:gap-2 sm:px-6">
                    <button wire:click="applyAdjustment('adjust_start')" 
                        class="inline-flex w-full justify-start rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm">
                        <i class="fas fa-step-forward mr-2"></i>
                        {{ __('1. Ajustar hora de inicio (retrasar entrada)') }}
                    </button>
                    <button wire:click="applyAdjustment('adjust_end')" 
                        class="mt-2 inline-flex w-full justify-start rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm">
                        <i class="fas fa-step-backward mr-2"></i>
                        {{ __('2. Ajustar hora de salida (adelantar salida)') }}
                    </button>
                    <button wire:click="applyAdjustment('adjust_schedule')" 
                        class="mt-2 inline-flex w-full justify-start rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        {{ __('3. Distribuir en tramos horarios') }}
                    </button>
                    <button wire:click="cancelAdjustment" 
                        class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm">
                        {{ __('Cancelar') }}
                    </button>
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
