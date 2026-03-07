# User Manual - CTH (Time and Schedule Control)

Welcome to the CTH user manual, your comprehensive time and schedule control system. This manual will guide you step by step to make the most of all application features.

## 📋 Table of Contents

1. [Introduction and Access](#1-introduction-and-access)
2. [Dashboard (Main Panel)](#2-dashboard-main-panel)
3. [Clock-In System and SmartClockIn](#3-clock-in-system-and-smartclockin)
4. [Event Management](#4-event-management)
5. [Workday Duration Control](#5-workday-duration-control)
6. [Calendar and Schedules](#6-calendar-and-schedules)
7. [Reports and Statistics](#7-reports-and-statistics)
8. [Personal Settings](#8-personal-settings)
9. [Team Functions](#9-team-functions)
10. [Frequently Asked Questions](#10-frequently-asked-questions)
11. [Troubleshooting](#11-troubleshooting)

---

## 1. Introduction and Access

### What is CTH?

CTH (Time and Schedule Control) is a web application designed to manage work time, clock-ins, schedules, and productivity statistics in an intuitive and efficient way.

### Main Features

- ✅ **SmartClockIn**: Intelligent automatic entry and exit system.
- ✅ **Event Management**: Creation and editing of time records (clock-ins, pauses, etc.).
- ✅ **Integrated Calendar**: Complete view of schedules and events.
- ✅ **Detailed Reports**: Statistics and productivity metrics.
- ✅ **Team Management**: Collaboration and supervision of work teams.
- ✅ **Holidays**: Automatic import of labor calendars.

### System Access

#### First Access
1. **Open your web browser** and go to the URL provided by your administrator.
2. **Click "Register"** if it's your first time.
3. **Complete the form** with your data:
   - Full name
   - First surname
   - Second surname
   - **DNI / NIE** (Mandatory identification)
   - Email address
   - Secure password
   - User code (provided by your administrator)

![Registration Screen](images/registro-usuario.png)
*Caption: Registration interface for new users in the CTH system.*

#### Regular Access
1. **Enter your email address**.
2. **Enter your password**.
3. **Check "Remember Me"** if you want to keep the session active.
4. **Click "Login"**.

![Login Screen](images/login-usuario.png)
*Caption: System login form.*

#### Password Recovery
If you forgot your password:
1. **Click "Forgot your password?"**.
2. **Enter your email address**.
3. **Check your email** for the recovery link.
4. **Follow the instructions** in the received email.

---

## 2. Dashboard (Main Panel)

### Dashboard Overview

The **Dashboard** is your main control center where you can see all relevant information at a glance.

![Dashboard Overview](images/dashboard-principal.png)
*Caption: General view of the Dashboard with metrics and quick access.*

### Dashboard Elements

#### A. Top Navigation Bar
- **CTH Logo**: Return to Dashboard from any page.
- **Main Menu**: Quick access to all sections.
- **Notifications**: Important alerts and messages (bell icon).
- **User Profile**: Personal settings and language change (avatar).

#### B. Main Metrics (Cards)
The top **Cards** show key information:

1. **Hours Worked Today**: Total time accumulated on the current day.
2. **Pending Events**: Number of unclosed records (e.g., a started workday).
3. **Weekly Hours**: Total hours worked in the current week.
4. **Productivity**: Performance metric based on your configured goals.

![Dashboard Metrics](images/metricas-dashboard.png)
*Caption: Detail of the metrics cards in the main panel.*

#### C. Charts and Statistics
The Dashboard includes visualizations such as the **Pie chart** for time distribution and bar charts for weekly evolution.

---

## 3. Clock-In System and SmartClockIn

### Available Clock-In Types

#### A. SmartClockIn (Intelligent Clock-In)
**SmartClockIn** is CTH's advanced system that automatically detects when you should perform a clock-in.

**How does it work?**
1. **Detection**: The system analyzes your schedule and usage patterns.
2. **Suggestions**: It proposes a **Clock-in** (entry) or **Clock-out** (exit) when appropriate.
3. **Confirmation**: With a single click, you can validate the proposed action.

![SmartClockIn](images/smart-clockin.png)
*Caption: SmartClockIn interface suggesting a clock-in.*

#### B. Manual Clock-In
For situations where you prefer full control or the automatic system is not applicable.

**Steps for manual clock-in:**
1. **Click "New Event"** on the Dashboard.
2. **Select event type**: Entry, Exit, Break, etc.
3. **Complete information**: Date, time, and a brief description.
4. **Click "Save"**.

#### C. Exceptional Clock-In
If you attempt to clock in outside the allowed time margin of your schedule, the system will allow an **Exceptional Clock-In**. You will receive a link via email to validate this action if necessary.

#### D. Pause System (Pause/Resume)
CTH allows you to pause your workday easily.

- **Pause**: Stops the productive time counter. Useful for medical appointments or personal errands.
- **Resume**: Continues the workday from where it was left.

![Pause System](images/sistema-pausas.png)
*Caption: Pause and Resume buttons in the SmartClockIn interface.*

---

## 4. Event Management

### What is an Event?
In CTH, each time record (an entry, an exit, a pause) is called an **Event**.

### Event History
You can check all your records in the History section. Here you will see details such as duration, event type, and observations.

![Event History](images/historial-eventos.png)
*Caption: Historical list of events recorded by the user.*

---

## 5. Workday Duration Control

### Automatic Validation
CTH continuously monitors the total duration of your daily workday. The system:
1. **Calculates** total minutes worked in the day.
2. **Compares** with the maximum limit established for your shift.
3. **Blocks** workday extensions that exceed the legal or configured limit.

### Adjustment Assistant
If an action (such as move an event on the calendar) causes the maximum time to be exceeded, an **Adjustment Assistant** will appear allowing you to:
- **Adjust start time**: Delay entry to maintain duration.
- **Adjust end time**: Advance exit to comply with the limit.
- **Adjust proportionally**: Redistribute time between time slots.

### Workday Calculation (Equivalent Days)
The system automatically calculates how many working days your accumulated hours represent based on your configured **Work Schedule**. 
*For example: If you have worked 16 hours and your standard workday is 8h, the system will report "2 equivalent days", making it easier to read monthly totals.*

---

## 6. Calendar and Schedules

### Calendar View
The calendar allows you to graphically visualize your workday. You can switch between monthly, weekly, and daily views.

![Calendar View](images/calendario-mensual.png)
*Caption: Monthly calendar view with events colored by type.*

### Schedule Configuration
Your administrator assigns a base schedule, but you can check your shifts and holidays directly from this section. Holidays are automatically imported for easier planning.

---

## 7. Reports and Statistics

### Report Generation
You can export your clock-in data in several formats:
- **PDF**: Ideal for printing or official submission. Includes page breaks per worker, issuance date, and professional name formatting.
- **Excel/CSV**: For detailed data analysis.

#### Professional Name Format
In all reports and listings, workers appear identified under the standard: `DNI - Surnames, Name`. This format ensures perfect alphabetical sorting and unequivocal administrative identification.

#### Printing Traceability
Each page of the generated PDF reports contains the **exact date and time of issuance** in the upper right corner, ensuring you always know if you are consulting the latest version of the data.
*Caption: Tool to filter and export time reports.*

---

## 8. Personal Settings

### Profile and Preferences
From your profile, you can:
- Change your avatar photo.
- Update your password.
- **Change application language** (Spanish/English).
- Configure the access **Token** for the mobile application.

![Profile Settings](images/configuracion-perfil.png)
*Caption: Personal settings and security panel.*

---

## 9. Team Functions

If you have the **Administrator** or **Supervisor** role, you will have access to additional functions:
- View real-time status of team members.
- Approve exceptional clock-ins.
- Manage work centers and NFC tags for mobile clock-in.

---

## 10. Frequently Asked Questions

**What is a Token?**
A **Token** is a unique alphanumeric key that allows the mobile application to identify you securely without needing to enter your password constantly.

**Why can't I clock in?**
Make sure you are within the allowed time range or request an exceptional clock-in link from your supervisor.

---

## 11. Troubleshooting

If you experience issues with the **Dashboard** or the **SmartClockIn** system, try reloading the page (F5) or clearing your browser cache. For persistent issues, contact technical support providing your user ID.

---
*Manual updated: February 2026*
*Version: 1.0.1*
---

## 💖 Support the Project

If you find this project useful and would like to support its maintenance and continuous development, consider making a donation through Patreon:

👉 **[Support on Patreon](https://www.patreon.com/cw/CTH_ControlHorario)**

Any support is greatly appreciated and helps CTH stay free and open source for everyone.

---
*© 2025 CTH - Time and Schedule Control*
