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
                        // Only allow dragging user events, not holidays
                        return draggedEvent.id.startsWith('event_');
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

                    eventContent: function(info) {
                        const isOpen = info.event.extendedProps.is_open;
                        const title = info.event.title;
                        let iconHtml = '';

                        if (info.event.id.startsWith('event_')) {
                            iconHtml = isOpen
                                ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                                : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';
                        } else if (info.event.extendedProps.is_holiday) {
                            iconHtml = '<i class="ml-1 mr-2 fa-solid fa-calendar-day" style="color: #A3E635;"></i>';
                        }

                        return {
                            html: `<span class="fc-title">${iconHtml} ${title}</span>`
                        };
                    }
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
