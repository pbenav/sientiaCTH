# User Manual - CTH (Time and Schedule Control)

Welcome to the CTH user manual, your comprehensive time and schedule control system. This manual will guide you step by step to make the most of all application features.

## 📋 Table of Contents

1. [Introduction and Access](#introduction-and-access)
2. [Main Dashboard](#main-dashboard)
3. [Clock-In System](#clock-in-system)
4. [Event Management](#event-management)
5. [Calendar and Schedules](#calendar-and-schedules)
6. [Reports and Statistics](#reports-and-statistics)
7. [Personal Settings](#personal-settings)
8. [Team Functions](#team-functions)
9. [Frequently Asked Questions](#frequently-asked-questions)
10. [Troubleshooting](#troubleshooting)

---

## 1. Introduction and Access

### What is CTH?

CTH (Time and Schedule Control) is a web application designed to manage work time, clock-ins, schedules, and productivity statistics in an intuitive and efficient way.

### Main Features

- ✅ **Smart Clock-In**: Automatic entry and exit system
- ✅ **Event Management**: Creation and editing of time events
- ✅ **Integrated Calendar**: Complete view of schedules and events
- ✅ **Detailed Reports**: Statistics and productivity metrics
- ✅ **Team Management**: Team collaboration and supervision
- ✅ **Holidays**: Automatic import of holidays

### System Access

#### First Access
1. **Open your web browser** and go to the URL provided by your administrator
2. **Click "Register"** if it's your first time
3. **Complete the form** with your data:
   - Full name
   - First surname
   - Second surname
   - Email address
   - Secure password
   - User code (provided by your administrator)

![Registration Screen](images/registro-usuario.png)
*Registration screen for new users*

#### Regular Access
1. **Enter your email address**
2. **Enter your password**
3. **Check "Remember Me"** if you want to keep the session active
4. **Click "Login"**

![Login Screen](images/login-usuario.png)
*System login screen*

#### Password Recovery
If you forgot your password:
1. **Click "Forgot your password?"**
2. **Enter your email address**
3. **Check your email** for the recovery link
4. **Follow the instructions** in the received email

---

## 2. Main Dashboard

### Dashboard Overview

The dashboard is your main control center where you can see all relevant information at a glance.

![Main Dashboard](images/dashboard-principal.png)
*Main dashboard overview*

### Dashboard Elements

#### A. Top Navigation Bar
- **CTH Logo**: Return to dashboard from any page
- **Main Menu**: Quick access to all sections
- **Notifications**: Important alerts and messages (bell icon)
- **User Profile**: Personal settings (avatar)

#### B. Main Metrics (Cards)
The top cards show key information:

1. **Hours Worked Today**
   - Total time worked on current day
   - Real-time updates
   - Color coding: Green (complete), Yellow (partial), Red (insufficient)

2. **Pending Events**
   - Number of unclosed events
   - Direct link to manage them
   - Visual notification if there are old events

3. **Weekly Hours**
   - Total hours worked in the week
   - Comparison with target hours
   - Visual progress percentage

4. **Productivity**
   - Performance metric based on objectives
   - Trend compared to previous periods
   - Improvement indicators

![Dashboard Metrics](images/metricas-dashboard.png)
*Cards with main metrics*

#### C. Charts and Statistics

**Hours Worked Chart**
- Visualization of the last 4 weeks
- Comparison between worked hours and targets
- Pattern and trend identification

![Hours Chart](images/grafico-horas.png)
*Chart showing worked hours evolution*

**Time Distribution**
- Pie chart showing how time is distributed
- Categories: Regular work, Overtime, Breaks, etc.
- Helps identify areas for improvement

#### D. Recent Events
List of the latest recorded events:
- **Date and time** of the event
- **Event type** (Entry, Exit, Break)
- **Duration** (if applicable)
- **Status** (Open/Closed)
- **Quick actions** (Edit/View details)

### Dashboard Quick Actions

#### Quick Clock-In Button
The most prominent dashboard button allows quick clock-in:

```
┌─────────────────────────────┐
│     🕐 CLOCK IN NOW        │
│                             │
│   [  ENTRY / EXIT  ]       │
│                             │
│  Last clock-in: 09:30      │
│  Status: Working           │
└─────────────────────────────┘
```

**Possible states:**
- **"Start Workday"**: When you haven't clocked in
- **"Break"**: During work hours
- **"Return from Break"**: When you're on pause
- **"End Workday"**: When finishing the day

#### Current Time Widget
Shows real-time information:
- Current system time
- Time elapsed since last clock-in
- Time remaining until end of workday
- Automatic reminders

---

## 3. Clock-In System

### Available Clock-In Types

#### A. Automatic Clock-In (Smart Clock-In)
The most advanced system that automatically detects when you should clock in.

**How it works:**
1. **Intelligent Detection**: The system analyzes your location, schedule, and patterns
2. **Automatic Suggestions**: Proposes clock-in when appropriate
3. **One Click**: You only need to confirm the action

![Smart Clock-In](images/smart-clockin.png)
*Smart clock-in interface*

**Smart Clock-In Configuration:**
1. Go to **Settings > Smart Clock-In**
2. **Enable geolocation** if you work from specific locations
3. **Configure your usual schedules**
4. **Set automatic reminders**

#### B. Manual Clock-In
For special situations or when you prefer total control.

**Steps for manual clock-in:**
1. **Click "New Event"** on the dashboard
2. **Select event type:**
   - Entry (Start of workday)
   - Exit (End of workday)
   - Break Start
   - Break End
   - Custom Event

![Manual Event Creation](images/evento-manual.png)
*Form to create manual event*

3. **Complete the information:**
   - **Date and time**: Auto-filled with current
   - **Description**: Optional, useful for future reference
   - **Observations**: Additional notes if necessary

4. **Click "Save Event"**

#### C. Exceptional Clock-In
For situations outside normal schedule.

**When to use exceptional clock-in:**
- Work outside established hours
- Weekends or holidays
- Emergency situations
- Unplanned remote work

**Exceptional clock-in process:**
1. **The system detects** you're outside hours
2. **An alert appears** asking if you want to make exceptional clock-in
3. **You confirm the action** and provide justification
4. **The event is created** marked as exceptional

![Exceptional Clock-In](images/fichaje-excepcional.png)
*Confirmation dialog for exceptional clock-in*

### Clock-In States

#### Visual Indicators
The system uses colors to show your current state:

- 🟢 **Green**: Working normally
- 🟡 **Yellow**: On break
- 🔴 **Red**: Outside work hours
- 🔵 **Blue**: Special or exceptional event

#### Current Status Panel
```
┌─────────────────────────────────────┐
│  Current Status: 🟢 WORKING        │
│                                     │
│  Entry: 09:00                      │
│  Elapsed time: 3h 45m              │
│  Scheduled break: 13:00            │
│                                     │
│  [ Go to Break ]  [ End Workday ]  │
└─────────────────────────────────────┘
```

### Clock-In History

#### History View
Go to **Events > History** to see all your clock-ins:

![Event History](images/historial-eventos.png)
*Complete clock-in history list*

**Information shown:**
- **Event ID**: Unique identifier
- **Date and Time**: When the clock-in occurred
- **Type**: Entry, Exit, Break, etc.
- **Duration**: Total event time
- **Status**: Open/Closed
- **Actions**: Edit, View details, Delete

#### Available Filters
- **By dates**: Specific range
- **By event type**: Only entries, only exits, etc.
- **By status**: Open/closed events
- **By description**: Text search

### Clock-In Corrections

#### When to correct a clock-in:
- You forgot to clock in at the correct time
- Error in recorded time
- Change in event type
- Add description or observations

#### Correction Process
1. **Locate the event** in history
2. **Click "Edit"** (pencil icon)
3. **Modify necessary fields**:

![Event Editing](images/editar-evento.png)
*Event editing modal*

   - **Start date/time**
   - **End date/time**
   - **Description**
   - **Observations**
   - **Event type**

4. **Save changes**

> **⚠️ Important**: You can only edit your own events and within the period allowed by your administrator.

---

## 4. Event Management

### Event Creation

#### Access to Event Creation
- **From Dashboard**: "New Event" button
- **From Calendar**: Click on any day/hour
- **From Events**: "Create Event" button

#### Complete Event Form

![Complete Event Form](images/formulario-evento.png)
*Complete form to create an event*

**Available fields:**

1. **Event Type** (Required)
   - Entry
   - Exit
   - Break
   - Meeting
   - Remote Work
   - Others (customizable)

2. **Start Date and Time** (Required)
   - Intuitive date picker
   - Time picker in 24h format
   - "Now" button to use current time

3. **End Date and Time**
   - Only for events with duration
   - Automatically calculated for some types
   - Validation to avoid overlaps

4. **Description**
   - Free text field
   - Useful for future references
   - Will auto-complete with type name if left empty

5. **Observations**
   - Long text field
   - For additional notes
   - Contextual information

6. **Work Center**
   - Automatic selection based on your configuration
   - Manual change if you work from multiple locations

### Special Event Types

#### All-Day Events
For holidays, vacations, or events that last the entire workday:

1. **Check "All day" checkbox**
2. **Only specify the date** (not time)
3. **The system will automatically calculate** duration

#### Recurring Events
For events that repeat regularly:

1. **Create the initial event**
2. **Mark "Recurring event"**
3. **Specify frequency:**
   - Daily
   - Weekly
   - Monthly
   - Custom

#### Overtime Events
The system automatically identifies overtime:

- **Events outside normal hours** are automatically marked
- **Only "workday" type events are NOT overtime**
- **You can manually override** automatic detection

### Advanced Event Management

#### Bulk Editing
To modify multiple events at once:

1. **Go to Events > List**
2. **Select events** (checkboxes)
3. **Use "Bulk Actions"**:
   - Change type
   - Update description
   - Close events
   - Export selection

![Bulk Editing](images/edicion-masiva.png)
*Interface for bulk event editing*

#### Event States

**Open Event** 🟢
- Ongoing event
- Can be modified freely
- Counts for current time

**Closed Event** 🔴
- Finished event
- Limited modification
- Doesn't affect current time

**Pending Event** 🟡
- Future scheduled event
- Free modification
- Will activate automatically

### Validations and Restrictions

#### Automatic Validations
- **No overlap**: You can't have two simultaneous events
- **Temporal order**: End time must be after start time
- **Duration limits**: Events can't exceed 24 hours
- **Future dates**: Future event limitation according to configuration

#### Role-based Restrictions
- **Regular user**: Only their own events
- **Supervisor**: Their team's events
- **Administrator**: All events

---

## 5. Calendar and Schedules

### Calendar View

#### Calendar Access
- **Main menu > Calendar**
- **Dashboard > Calendar widget**
- **Events > Calendar view**

#### Available Views

![Monthly Calendar View](images/calendario-mensual.png)
*Monthly calendar view with events*

**Monthly View**
- Complete month overview
- Events shown as colored dots
- Quick navigation between months
- Hours summary per day

**Weekly View**
- Complete week detail
- Events with specific schedules
- Ideal for detailed planning
- Conflict visualization

![Weekly Calendar View](images/calendario-semanal.png)
*Weekly view with detailed events*

**Daily View**
- Detailed day timeline
- Events with visual duration
- Clearly visible free spaces
- Hour-by-hour planning

#### Color Legend
The calendar uses an intuitive color code:

- 🟢 **Green**: Regular work
- 🔵 **Blue**: Breaks
- 🟡 **Yellow**: Meetings
- 🟠 **Orange**: Remote work
- 🔴 **Red**: Overtime
- ⚪ **Gray**: Holidays

### Calendar Interaction

#### Create Events from Calendar
1. **Click on any day/hour**
2. **Creation modal opens** with preselected date
3. **Complete event information**
4. **Event appears immediately** on calendar

#### Edit Existing Events
1. **Click on any calendar event**
2. **Information modal opens**
3. **Click "Edit"** to modify
4. **Changes are reflected** instantly

#### Quick Navigation
- **Side arrows**: Previous/next month/week
- **"Today" button**: Return to current day
- **Month/year selector**: Quick navigation to any period

### Work Schedules

#### Schedule Configuration

![Schedule Configuration](images/configuracion-horarios.png)
*Work schedule configuration panel*

**Standard Schedule:**
- Monday to Friday: 9:00 - 18:00
- Break: 13:00 - 14:00
- Weekends: Non-working

**Flexible Schedules:**
- Flexible entry: 8:00 - 10:00
- Exit automatically adjusted
- Minimum hours per day: 8 hours

**Rotating Shifts:**
- Morning shift: 6:00 - 14:00
- Afternoon shift: 14:00 - 22:00
- Night shift: 22:00 - 6:00

#### Holiday Management

**Automatic Import:**
1. **Go to Settings > Holidays**
2. **Select the year** you want to import
3. **Choose "Import All"** or select specific days
4. **Days are automatically marked** on calendar

![Holiday Import](images/importar-festivos.png)
*Holiday import modal*

**Custom Holidays:**
- Company-specific dates
- Vacation closure days
- Special team events

### Planning and Reminders

#### Automatic Reminders
The system can send you reminders:

- **15 minutes before** workday start
- **5 minutes before** break end
- **At the end** of workday
- **Important scheduled events**

#### Weekly Planning
Special view to plan your week:

1. **Go to Calendar > Weekly Planning**
2. **Drag and drop** events to reorganize
3. **Set objectives** for hours per day
4. **System automatically calculates** totals

---

## 6. Reports and Statistics

### Available Report Types

#### A. Hours Worked Report

![Hours Report](images/informe-horas.png)
*Detailed hours worked report*

**Information included:**
- Total hours for the period
- Daily breakdown
- Regular vs. overtime hours
- Comparison with objectives
- Trends and averages

**Available filters:**
- Customizable date range
- By event type
- By work center
- Include/exclude overtime

#### B. Productivity Report

**Metrics included:**
- Average punctuality
- Schedule compliance
- Work patterns
- Time efficiency
- Team comparisons

#### C. Absence Report

**Detailed information:**
- Non-worked days
- Absence reasons
- Absence patterns
- Impact on objectives
- Recommendations

### Report Generation

#### Basic Report
1. **Go to Reports > Generate Report**
2. **Select report type**
3. **Set the period** (last week, month, quarter, custom)
4. **Apply filters** if necessary
5. **Click "Generate"**

![Report Generator](images/generador-informes.png)
*Interface to generate custom reports*

#### Advanced Report
For deeper analysis:

1. **Select "Advanced Report"**
2. **Choose multiple metrics:**
   - Hours worked
   - Punctuality
   - Productivity
   - Comparisons
3. **Configure charts** and visualizations
4. **Set comparisons** with previous periods

### Visualizations and Charts

#### Trend Chart
Shows the evolution of your metrics over time:

![Trend Chart](images/grafico-tendencias.png)
*Temporal evolution chart of worked hours*

**Available analysis:**
- General trend (ascending/descending)
- Weekly patterns
- Seasonal variations
- Improvement points

#### Distribution Chart
Pie chart showing how you distribute your time:

- Productive work: 75%
- Breaks: 15%
- Meetings: 8%
- Others: 2%

#### Team Comparisons
If you're a supervisor, you can see comparisons:

```
┌─────────────────────────────┐
│  Team Comparison            │
├─────────────────────────────┤
│  John:    ████████░░ 8.2h  │
│  Mary:    ██████████ 8.5h  │
│  Peter:   ███████░░░ 7.8h  │
│  Anna:    █████████░ 8.1h  │
│                             │
│  Team average: 8.15h       │
│  Target: 8.0h              │
└─────────────────────────────┘
```

### Data Export

#### Available Formats
- **PDF**: For presentations and printing
- **Excel**: For additional analysis
- **CSV**: To import into other tools
- **JSON**: For technical integrations

#### Export Process
1. **Generate the report** you want to export
2. **Click "Export"**
3. **Select format**
4. **Configure additional options**:
   - Include charts
   - Detail level
   - Applied filters
5. **Download file**

### Alerts and Notifications

#### Automatic Alerts
The system can alert you about:

- **Insufficient hours** in the week
- **Irregular work patterns**
- **Unmet objectives**
- **Detected improvements**

#### Alert Configuration
1. **Go to Settings > Notifications**
2. **Activate alerts** you want to receive
3. **Configure custom thresholds**
4. **Choose notification method** (email, dashboard, both)

---

## 7. Personal Settings

### Settings Access
- **Click on your avatar** (top right corner)
- **Select "Settings"** from dropdown menu
- **Or go to Settings** from main menu

### Personal Profile

#### Basic Information

![Profile Configuration](images/configuracion-perfil.png)
*Personal profile configuration panel*

**Editable data:**
- **Profile photo**: Upload image (max 2MB)
- **Full name**
- **Surnames**
- **Email address**
- **Phone** (optional)
- **Employee code**

#### Password Change
1. **Go to "Security" section**
2. **Enter your current password**
3. **Type new password** (min. 8 characters)
4. **Confirm new password**
5. **Click "Update Password"**

**Password requirements:**
- Minimum 8 characters
- At least one uppercase letter
- At least one number
- At least one special character

### Work Preferences

#### Personal Schedules
- **Preferred entry time**
- **Preferred exit time**
- **Break duration**
- **Working days** (if you have special schedule)

#### Work Center
- **Main center**: Your usual location
- **Secondary centers**: Other locations where you work
- **Remote work**: Telework configuration

### Notification Settings

#### Notification Types

![Notification Settings](images/configuracion-notificaciones.png)
*Notification configuration panel*

**Clock-In Notifications:**
- ✅ Entry reminder
- ✅ Break reminder
- ✅ Exit reminder
- ✅ Pending clock-ins

**Report Notifications:**
- ✅ Automatic weekly report
- ✅ Objective alerts
- ✅ Performance comparisons

**System Notifications:**
- ✅ Important updates
- ✅ Scheduled maintenance
- ✅ New features

#### Notification Channels
- **Email**: Email notifications
- **Dashboard**: In-app alerts
- **Browser**: Push notifications (if enabled)

### Privacy Settings

#### Data Control
- **Share statistics** with team
- **Show status** in real-time
- **Allow comparisons** with colleagues
- **Data in supervisor reports**

#### Session Configuration
- **Auto logout** after inactivity
- **Remember device** for future access
- **Require confirmation** for critical actions

### Language and Localization

#### Regional Settings
- **Interface language**: Spanish, English
- **Date format**: DD/MM/YYYY, MM/DD/YYYY
- **Time format**: 24h, 12h (AM/PM)
- **Time zone**: Automatic based on location

#### Visual Customization
- **Theme**: Light, Dark, Automatic
- **Font size**: Small, Normal, Large
- **Information density**: Compact, Normal, Spacious

---

## 8. Team Functions

*This section is relevant if you are a supervisor, administrator, or have special team permissions.*

### Team Management

#### Team Overview

![Team Dashboard](images/dashboard-equipo.png)
*Control panel for team supervisors*

**Available information:**
- **Active team members**
- **Current status** of each member
- **Consolidated team statistics**
- **Group alerts and notifications**

#### Member Administration

**Add New Members:**
1. **Go to Team > Manage Members**
2. **Click "Invite Member"**
3. **Enter new member's email**
4. **Assign role** (Member, Supervisor)
5. **Send invitation**

**Permission Management:**
- **Member**: Can only view their data
- **Supervisor**: Can view team data
- **Administrator**: Full team control

### Clock-In Supervision

#### Supervision Panel

![Supervision Panel](images/panel-supervision.png)
*Team clock-in supervision view*

**Real-time information:**
- **Current status** of each member
- **Hours worked** in the day
- **Pending or problematic events**
- **Compliance alerts**

#### Event Approval
As a supervisor, you can approve special events:

1. **Exceptional events** outside hours
2. **Clock-in corrections**
3. **Permits** and absences
4. **Unscheduled overtime**

### Team Reports

#### Consolidated Reports
- **Complete team productivity**
- **Individual comparisons**
- **Group trends**
- **Objectives vs. results**

#### Performance Analysis
- **Identification of problematic patterns**
- **Recognition** of good performance
- **Improvement suggestions**
- **Resource planning**

### Team Schedule Management

#### Shift Planning
- **Assignment** of individual schedules
- **Shift rotation**
- **Absence coverage**
- **Remote work coordination**

#### Holidays and Vacations
- **Shared team calendar**
- **Coordinated absence management**
- **Coverage planning**
- **Vacation approval**

---

## 9. Frequently Asked Questions

### About Clock-Ins

**Q: What do I do if I forgot to clock in for entry?**
A: You can create a manual event from the dashboard or calendar. Go to "New Event", select "Entry", adjust the correct time and add an observation explaining the situation.

**Q: Can I clock in from my mobile?**
A: Yes, CTH is completely responsive. Access from your mobile browser using the same URL and you'll have all functions available.

**Q: Does the system detect my location?**
A: Only if you authorize it. Smart Clock-In can use geolocation to suggest automatic clock-ins when you arrive or leave work.

**Q: What happens if I work outside hours?**
A: The system will detect that you're outside normal hours and ask if you want to make an "exceptional clock-in". These events are specially marked for your supervisor.

### About Events and Calendar

**Q: Can I schedule future events?**
A: Yes, you can create events for future dates. This is useful for planning meetings, remote work, or scheduled appointments.

**Q: How do I correct an error in an event?**
A: Go to event history, find the event you need to correct and click "Edit". You can modify date, time, type, and description.

**Q: Do events sync with my personal calendar?**
A: Currently there's no automatic sync, but you can export your events in iCal format to import them into your personal calendar.

### About Reports

**Q: How often are my statistics updated?**
A: Dashboard statistics update in real-time. Detailed reports are processed every hour to include the latest changes.

**Q: Can I share my reports?**
A: You can export your reports in PDF or Excel and share them manually. Supervisors can access consolidated team reports.

**Q: Why don't my overtime hours appear correctly?**
A: The system automatically calculates overtime based on your configured schedule. If there are discrepancies, check your schedule settings or contact your supervisor.

### About Settings

**Q: Can I change my work schedule?**
A: Schedule changes generally require supervisor approval. You can request the change from Settings > Schedules.

**Q: How do I disable email notifications?**
A: Go to Settings > Notifications and uncheck "Email notifications" or customize which types of notifications you want to receive.

**Q: Is my data secure?**
A: Yes, CTH implements security best practices. Your data is encrypted and only accessible by you and, when appropriate, by your direct supervisor.

---

## 10. Troubleshooting

### Common Problems

#### Can't Access the System

**Symptoms:**
- Page doesn't load
- Connection error
- "Site unavailable" message

**Solutions:**
1. **Check your internet connection**
2. **Try from another browser** (Chrome, Firefox, Safari)
3. **Clear browser cache** (Ctrl+F5)
4. **Verify URL** with your administrator
5. **Contact technical support** if problem persists

#### Clock-In Problems

**Symptom: "Can't create events"**

**Possible causes and solutions:**
- **Restricted schedule**: Check if you have permissions to clock in at that time
- **Duplicate event**: Verify you don't have another active event
- **Insufficient permissions**: Contact your supervisor
- **Network error**: Refresh page and try again

**Symptom: "Smart Clock-In doesn't work"**

**Solutions:**
1. **Enable geolocation** in your browser
2. **Check your schedule configuration**
3. **Use manual clock-in** as alternative
4. **Contact support** for advanced configuration

#### Performance Problems

**Symptom: "Application is slow"**

**Solutions:**
1. **Close other browser tabs**
2. **Update browser** to latest version
3. **Clear browser cache**
4. **Check your internet connection**
5. **Try during off-peak hours**

### Support Contact

#### Information to Provide
When contacting support, include:

- **Application URL**
- **Browser and version** you're using
- **Detailed description** of the problem
- **Steps** you performed before the error
- **Exact error message** (if applicable)
- **Screenshots** of the problem

#### Support Channels
- **Email**: support@cth-app.com
- **Phone**: +XX-XXX-XXX-XXXX
- **Hours**: Monday to Friday, 9:00-18:00
- **Emergencies**: Available 24/7 for critical issues

### Optimization Tips

#### For Better Performance
- **Use modern browsers** (Chrome 90+, Firefox 88+, Safari 14+)
- **Keep browser extensions updated**
- **Avoid multiple CTH tabs** open
- **Log out** when finishing the day

#### For Better Experience
- **Customize your notifications** according to your needs
- **Use keyboard shortcuts** when available
- **Set up reminders** for important clock-ins
- **Regularly review** your statistics

---

## 📞 Support and Contact

### Additional Resources

- **Technical documentation**: Available in Help menu
- **Tutorial videos**: Coming soon
- **User community**: Company internal forum
- **Updates**: Automatic notifications of new features

### Feedback and Suggestions

Your opinion is important to us! You can send suggestions through:
- **Feedback form** in the application
- **Direct email**: feedback@cth-app.com
- **Quarterly improvement meetings** with users

---

*Manual updated: November 6, 2025*
*System version: CTH 2025.11*
*© 2025 CTH - Time and Schedule Control*