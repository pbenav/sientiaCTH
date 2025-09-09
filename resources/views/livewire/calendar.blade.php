<div>
    @livewire('add-event-modal')
    @livewire('edit-event-modal')
    <div id='calendar-container' wire:ignore>
        <div id='calendar'></div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
    <script>
        document.addEventListener('livewire:load', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: @json($this->getEvents()),
                editable: true,
                selectable: true,

                // Callback for clicking an event
                eventClick: function(info) {
                    @this.emit('showEditEventModal', info.event.id);
                },

                // Callback for clicking a date
                dateClick: function(info) {
                    @this.emit('showAddEventModal', info.dateStr);
                },

                // Callback for dragging and dropping an event
                eventDrop: function(info) {
                    if (!confirm("Are you sure about this change?")) {
                        info.revert();
                    } else {
                        @this.emit('eventDrop', info.event.id, info.event.start.toISOString(), info.event.end.toISOString());
                    }
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
