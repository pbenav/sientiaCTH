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
                        // Permitir el drop salvo que la propiedad editable exista y sea false.
                        // Algunos draggedEvent (p. ej. externos) no tienen la propiedad editable definida,
                        // y en ese caso FullCalendar debe permitir el drop si no está explícitamente denegado.
                        return !(draggedEvent && draggedEvent.editable === false);
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

                window.addEventListener('refresh-calendar', event => {
                    calendar.removeAllEvents();
                    calendar.addEventSource(event.detail.events);
                });
            });
        </script>
    @endpush
</div>
