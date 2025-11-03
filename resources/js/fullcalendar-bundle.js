/**
 * FullCalendar Bundle
 * Imports all necessary plugins for the application
 */

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

// Make Calendar and plugins available globally
window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    timeGridPlugin,
    listPlugin,
    interactionPlugin,
    esLocale
};
