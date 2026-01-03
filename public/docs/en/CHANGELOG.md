# 📜 Changelog - CTH

## Historical Summary

This document collects the most important milestones in the evolution of **CTH (Time & Schedule Control)**, from its creation in May 2022 to the present.

---

## 🎓 January 2026 - Permission System v1.0 and Comprehensive Documentation

### Granular Permission System
- **PermissionMatrix**: Centralized matrix with over 60 defined permissions
- **System Roles**: Administrator, Editor, User, and Inspector
- **Contextual Permissions**: Validation by team and specific resource
- **Team Limits**: Control of how many teams each member can create (`teams.limits.manage`)
- **Idempotent Migration**: Safe system for updating existing databases

### Documentation and Localization
- **Documentation Viewer**: Navigable system integrated into the application
- **Consolidated Manuals**: User, Developer, Mobile API, Migrations
- **Multilingual Support**: Complete documentation in Spanish and English
- **Language Filtering**: Content automatically adapted to user's language
- **Internal Links**: Smooth navigation between documents with anchors

### Optimization and Performance
- **Migration Consolidation**: Unified initial schema for clean installations
- **Strategic Indexes**: Optimization of frequent queries
- **Welcome Team**: Automatic initial team for new users
- **Null-Safety**: Improvements in Livewire components to prevent errors

---

## 📱 December 2025 - Professional Reports and Enhanced Dashboard

### Advanced Reporting System
- **PDF Generation with Browsershot**: High-quality PDFs using Puppeteer/Chromium
  - Automatic page numbering
  - Optimized landscape design
  - Complete metadata (totals, averages, summaries)
- **Alternative mPDF Engine**: Fallback for environments without Node.js
- **Multiple Export**: Excel, CSV and PDF
- **Specific Translations**: Dedicated namespace for reports (`reports.`)
- **Loading Indicators**: SweetAlert during report generation

### Control Dashboard
- **Unified Panel**: Key statistics in consolidated view
- **Inbox Summary**: Pending and recent messages
- **Accordion Announcements**: Compact visualization of team notices
- **Broadcast Messages**: "Message to All" button for mass communications
- **Visual KPIs**: Real-time performance indicators

### Announcement System
- **Rich Editor**: Markdown support with preview
- **Scheduled Publishing**: Configurable start/end dates
- **Granular Permissions**: Control of creation/editing by role
- **HTML Sanitization**: HTMLPurifier with custom CSS

### View Improvements
- **Event Redesign**: Revamped interface with Tailwind CSS and Jetstream
- **Improved Responsive**: Full mobile adaptation
- **Optimized Calendar**: Viewport height and auto-scroll by work schedule

---

## 🚀 November 2025 - SmartClockIn and Complete Mobile API

### SmartClockIn
- **Smart Clock-In**: Button that automatically detects the next action
  - Start of workday
  - Pause/Resume
  - End of workday
  - Exceptional clock-in (outside schedule)
- **Schedule Validation**: Integration with user's work schedules
- **Pause System**: Complete management of breaks (`pause_event_id`)
- **Grace Periods**: Configurable flexibility for entry/exit

### RESTful Mobile API
- **Main Endpoints**:
  - `POST /api/v1/clock` - Smart clock-in with location
  - `POST /api/v1/status` - Current status and next action
  - `POST /api/v1/history` - Clock-in history with filters
  - `GET /api/v1/schedule` - User work schedules
  - `POST /api/v1/sync` - Offline synchronization
- **Simplified Authentication**: Login with unique `user_code`
- **NFC System**: NFC tag verification at work centers
- **Dynamic Configuration**: Automatic download of server parameters
- **ISO 8601 Format**: Standardization of weekdays (1-7)

### Internationalization
- **Status Codes**: Localizable messages in API responses
- **Complete Translations**: ES/EN throughout the application
- **Language Preference**: User configuration (`locale`)

### UX Improvements
- **Mobile Redesign**: Interface optimized for mobile devices
- **Calendar Auto-scroll**: Smart positioning by work schedule
- **Consistent Icons**: Unified design of buttons and navigation
- **Informative Tooltips**: Contextual help on key elements

---

## 📊 October 2025 - Work Centers and Security

### Work Centers
- **Complete Management**: CRUD of work locations
- **Team Association**: Each center belongs to a team
- **Structured Address**: Detailed fields (city, postal code, country)
- **Default Center**: User preference
- **Clock-In Integration**: Location recording in each event

### Holiday Management
- **Calendar by Team**: Independent holidays per team
- **Automatic Import**: External API for official Spanish holidays
- **Holiday Types**: Classification (national, regional, local)
- **Calendar Visualization**: Integration with FullCalendar (orange color)

### Messaging System
- **Internal Messages**: Communication between users
- **Conversation Threads**: Nested replies (`parent_id`)
- **Real-Time Notifications**: New message alerts
- **Inbox Management**: Mark as read, delete, archive
- **Mass Messages**: Broadcasting to the entire team

### Exceptional Clock-In
- **One-Time Tokens**: Secure generation for clock-ins outside schedule
- **Temporal Validation**: Tokens with configurable expiration
- **Admin Notifications**: Alerts for exceptional clock-ins
- **Specific Modal**: Dedicated interface to create exceptional entries

### Security
- **Advanced Login**: Anti brute-force protection with progressive lockouts
- **Complete Audit**: Recording of critical actions
- **Grace Periods**: Configurable tolerance in clock-ins
- **Automatic Closure**: `AutoCloseEvents` command for open events
- **Laravel 10 Upgrade**: Migration to LTS version with security improvements

### Time Zones
- **Timezone per Team**: Each team can define its time zone
- **Automatic Conversion**: Correct calculations independent of server
- **Shift Validation**: Support for shifts that cross midnight

---

## 🎯 September 2025 - Foundation of the Modern Era

### Initial Features
- **Clock-In System**: Implementation of basic entry/exit system
- **All-Day Events**: Support for full-day events (`is_all_day`)
- **Interactive Calendar**: FullCalendar integration with Spanish localization
- **Team Management**: Multi-team system with basic roles (Owner, Admin, Member, Inspector)
- **Event Types**: Event classification system with color codes
- **Statistics**: Real-time statistics panel with Chart.js

### Base Event Types
- Work Shift (green)
- Vacation (blue, authorizable)
- Personal Business (purple, authorizable)
- Pause (orange)
- Special Event (red)

### UX/UI Improvements
- Reactive modals with Livewire to create/edit events
- Real-time form validation system
- Responsive interface optimized for mobile
- Complete localization to Spanish (Spain)

---

## 📈 2023-2024 - Evolution and Refinement

### Year 2024: Consolidation
- **Query Optimization**: Refactoring of queries for better performance
- **Advanced Role System**: Contextual permissions per team
- **Improved Filtering**: Reactive components for event and user search
- **Excel Export**: Laravel Excel integration for reports
- **Data Validation**: Form improvements with real-time validation
- **Internationalization**: Expansion of translation system

### Year 2023: Feature Expansion
- **Dashboard with Charts**: Chart.js integration for visual statistics
- **Dynamic Reports**: Flexible report generation system
- **GetTimeRegisters Filters**: Reusable component for clock-in filtering
- **Event Observations**: Free text field for additional notes
- **GDPR Improvements**: Compliance with data protection regulations
- **CSV Export**: Alternative format for data analysis
- **User Code Search**: Quick filtering with `user_code`
- **Cache Optimization**: Reduction of redundant queries

---

## 🏗️ 2022 - Project Foundation

### May - June 2022: First Steps
- **Initial Commit** (May 1, 2022): Base structure with Laravel
- **Event System**: First implementation of entry/exit recording
- **Numpad Interface**: Numeric keyboard for quick clock-in with user code
- **User Codes**: Unique identification system (`user_code`) for each worker
- **Livewire Components**: Progressive migration from Blade to reactive components
- **Authentication**: Login with Laravel Jetstream

### July - September 2022: Building Foundations
- **User Management**: Complete CRUD of workers
- **Permissions by Role**: Initial authorization logic (Owner, Admin, Member)
- **Full Names**: Support for surnames (`family_name1`, `family_name2`)
- **Search and Filtering**: First functional versions
- **Localization**: Spanish date format (d/m/Y H:i)
- **Secure Session**: Automatic logout on inactivity
- **FullCalendar Calendar**: Initial integration with draggable events

### October - December 2022: Initial Refinement
- **Event Optimization**: Performance improvements in `events` table queries
- **Statistics v1**: First functional charts with worked hours
- **Event Types**: Basic classification (Shift, Vacation, Pause)
- **Color Codes**: Visual system to differentiate types
- **CSS Improvements**: Continuous interface refinement with Tailwind
- **Basic Reports**: First version of data export
- **Schedule Validation**: Initial logic to verify overlaps

---

## 🔧 Current Technical Features

### Technology Stack
- **Backend**: Laravel 10.x + Jetstream + Livewire 3.x
- **Frontend**: Tailwind CSS 3.x + Alpine.js + FullCalendar 6.x
- **Database**: MySQL 8.0+ with complete timezone support
- **API**: RESTful with Sanctum for mobile authentication
- **PDF**: Browsershot (Puppeteer/Chromium) + mPDF as fallback
- **Mobile**: Flutter with hybrid WebView and NFC synchronization

### External Integrations
- Spanish holiday API (official calendar)
- NFC system for contactless clock-ins
- Real-time push notifications
- Geolocation services

### Implemented Security
- Optional 2FA authentication (Fortify)
- Secure session tokens (Sanctum)
- Automatic HTML sanitization (HTMLPurifier)
- Complete audit of critical actions
- Rate limiting on API endpoints
- CSRF protection on all forms
- Secure password hashing (bcrypt)

---

## 📊 Project Statistics

- **Development Period**: May 2022 - January 2026 (44 months)
- **Total Commits**: 695+
- **Supported Languages**: Spanish, English
- **System Permissions**: 60+
- **Predefined Roles**: 4 (Administrator, Editor, User, Inspector)
- **API Endpoints**: 15+
- **Eloquent Models**: 20+
- **Livewire Components**: 25+
- **Documents**: 10+ manuals (multilingual)

---

## 🎯 Future Roadmap

### Upcoming Features
- **Advanced Reports**: User-customizable reports with dynamic filters
- **Configurable Dashboard**: Draggable widgets and saved preferences
- **Improved PWA**: Native mobile application with better offline support
- **Push Notifications**: Enhanced real-time alert system
- **Calendar Integration**: Bidirectional synchronization with Google Calendar and Outlook
- **Public API**: Documented endpoints for third-party integrations
- **Rotating Shifts**: Automatic shift schedule management
- **Absence Management**: Complete module for sick leave and permits
- **Basic Payroll**: Automatic hour calculation for payroll

### Planned Technical Improvements
- **Laravel 11**: Migration to next LTS version
- **Redis Cache**: Distributed cache implementation for better performance
- **Queue Workers**: Asynchronous processing of heavy tasks (reports, emails)
- **Websockets**: Real-time updates without polling
- **Automated Tests**: Increased coverage with PHPUnit and Pest
- **CI/CD Pipeline**: Complete deployment automation

---

**Last updated**: January 3, 2026  
**Current version**: 1.0.0  
**Developed by**: pbenav  
**License**: Proprietary
