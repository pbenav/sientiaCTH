<div>
    @livewire('add-event')
    @livewire('edit-event')
    @livewire('event-info-modal')
    
    <div id='calendar-container' wire:ignore>
        <div id='calendar'></div>
    </div>

    @push('scripts')
        <script src='{{ asset('js/fullcalendar-bundle.js') }}'></script>
        <script>
            document.addEventListener('livewire:load', function() {
                var calendarEl = document.getElementById('calendar');


                
                var calendar = new FullCalendar.Calendar(calendarEl, {
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

                    

                    
                    eventAllow: function(dropInfo, draggedEvent) {
                        // Allow drop unless the editable property exists and is false.
                        // Some draggedEvent (e.g. external) don't have the editable property defined,
                        // and in that case FullCalendar should allow the drop if not explicitly denied.
                        return !(draggedEvent && draggedEvent.editable === false);
                    },

                    // Callback for clicking an event
                    eventClick: function(info) {
                        // Only allow editing user events, not holidays
                        if (info.event.id.startsWith('event_')) {
                            const eventId = info.event.id.replace('event_', '');
                            // Check if event is closed by looking at the icon
                            const iconHtml = info.event.extendedProps.iconHtml || '';
                            const isClosed = iconHtml.includes('fa-lock') && !iconHtml.includes('fa-lock-open');
                            
                            if (isClosed) {
                                // Show info modal for closed events
                                @this.call('showEventInfo', eventId);
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
                                @this.emit('eventDrop', info.event.id.replace('event_', ''), info.event.start.toISOString(), info.event.end.toISOString());
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
                                @this.emit('eventResize', info.event.id.replace('event_', ''), info.event.start.toISOString(), info.event.end.toISOString());
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

                calendar.render();
                
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

                window.addEventListener('refresh-calendar', event => {
                    calendar.removeAllEvents();
                    calendar.addEventSource(event.detail.events);
                });
            });
        </script>
    @endpush
</div>
