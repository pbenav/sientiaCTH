# CTH Application Documentation

Welcome to the comprehensive documentation for the CTH (Time and Schedule Control) system.

## Documentation Index

### 📚 Main Documentation

#### User Guides
- **[User Manual](user-manual.md)** - Complete guide with screenshots and examples
- **[Installation Guide](installation-guide.md)** - Complete installation and configuration instructions
- **Administrator Manual** - Administrative functions and team management *(coming soon)*

#### Technical Documentation
- **[Command Reference](command-reference.md)** - Artisan commands and system utilities
- **[Migration Guide](migration-guide.md)** - Migration procedures and production deployment
- **Database Schemas** - Database structure and relationships *(coming soon)*
- **API Documentation** - Technical API reference *(coming soon)*

#### Security and Maintenance
- **[Security Audit](security-audit.md)** - Audit reports and security recommendations
- **[Security Patches](security-patches.md)** - History of applied security fixes
- **Security Policies** - Best practices and security policies *(coming soon)*

### 🔧 Main Features

#### Clock-In System
- **Smart Clock-In** - Intelligent automatic clock-in system
- **Exceptional Clock-Ins** - Management of clock-ins outside work hours
- **Time Events** - Creation and management of time events

#### Team Management
- **User Administration** - User and permission management
- **Work Centers** - Location and schedule configuration
- **Holidays** - Import and management of holidays

#### Reports and Statistics
- **Dashboard** - Control panel with main metrics
- **Time Reports** - Detailed attendance reports
- **Productivity Statistics** - Team performance metrics

### 🚀 New Features (November 2025)

#### Modal Improvements
- **Redesigned Modals** - Optimized interface for better space utilization
- **Responsive Modals** - Adaptive design for different screen sizes

#### Overtime System
- **New Overtime Logic** - Only "workday" type events are NOT overtime
- **Automatic Recalculation** - Automatic overtime recalculation system
- **Verification Commands** - Tools to verify and correct data

#### Holiday Import
- **Bulk Import** - "Import All" option for holidays
- **Multiple Selection** - Improved "Select All" checkbox
- **Holiday API** - Integration with external services

## 📝 Changelog

### Current Version (November 2025)
- ✅ Fixed null descriptions in SmartClockIn
- ✅ Description field in event modals
- ✅ New overtime logic implemented
- ✅ Production migrations with robust error handling
- ✅ Console commands documented
- ✅ Modal redesign for better UX
- ✅ "Import All" functionality in holidays

## 🛠️ For Developers

### Available Commands
```bash
# Event management commands
php artisan events:autoclose
php artisan events:fix-data
php artisan events:update-extra-hours
php artisan events:verify-and-fix

# Cache clearing
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Application Structure
- **Backend**: Laravel 11 with Livewire
- **Frontend**: Tailwind CSS + Alpine.js
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Jetstream
- **Real-time**: Livewire for interactivity

## 📞 Support

For questions or issues:
1. Check this documentation
2. Review application logs
3. Use available verification commands

---

*Last updated: November 6, 2025*