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
                    events: @json($this->getEvents()),
                    editable: true,
                    selectable: true,

                    // Callback for clicking an event
                    eventClick: function(info) {
                        @this.call('triggerEditModal', info.event.id);
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
                        if (!confirm("{{ __('Are you sure about this change?') }}")) {
                            info.revert();
                        } else {
                            @this.emit('eventDrop', info.event.id, info.event.start.toISOString(), info
                                .event.end.toISOString());
                        }
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
