<div>
    @livewire('add-event')
    @livewire('edit-event')
    <div id='calendar-container' wire:ignore>
        <div id='calendar'></div>
    </div>

    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/locales-all.global.min.js'></script>
        <script>
            document.addEventListener('livewire:load', function() {
                var calendarEl = document.getElementById('calendar');

                var calendar = new FullCalendar.Calendar(calendarEl, {
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
                    initialView: 'timeGridWeek',
                    events: @json($this->getEvents()),
                    editable: true,
                    eventDurationEditable: true,
                    selectable: true,
                    eventAllow: function(dropInfo, draggedEvent) {
                        // Use the 'editable' property passed from the backend
                        return draggedEvent.extendedProps.editable && draggedEvent.id.startsWith('event_');
                    },

                    // Callback for clicking an event
                    eventClick: function(info) {
                        // Only allow editing user events, not holidays
                        if (info.event.id.startsWith('event_')) {
                            @this.call('triggerEditModal', info.event.id.replace('event_', ''));
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
                                @this.emit('eventDrop', info.event.id, info.event.start.toISOString(), info.event.end.toISOString());
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
                                @this.emit('eventResize', info.event.id, info.event.start.toISOString(), info.event.end.toISOString());
                            } else {
                                info.revert();
                            }
                        });
                    },

                    eventContent: function(arg) {
                        let iconHtml = '';
                        // Check the custom property from the backend
                        if (arg.event.extendedProps.is_open === false) {
                            iconHtml = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg> `;
                        }
                        return { html: `<div class="fc-event-main-frame">${iconHtml}<div class="fc-event-title-container"><div class="fc-event-title fc-sticky">${arg.event.title}</div></div></div>` };
                    },
                });

                calendar.render();

                window.addEventListener('refresh-calendar', event => {
                    calendar.removeAllEvents();
                    calendar.addEventSource(event.detail.events);
                });
            });
        </script>
    @endpush
</div>
