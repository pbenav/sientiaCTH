# 🛠️ Available Console Commands

This document describes all available console (Artisan) commands for event management and system maintenance.

## 📋 Event Management Commands

### 1. `events:autoclose`
**Purpose**: Automatically closes unconfirmed events that have passed their expiration date

```bash
php artisan events:autoclose
```

**Functionality**:
- ✅ Reviews all teams with configured `event_expiration_days`
- ✅ Closes open events that exceed the time limit
- ✅ Logs each closed event
- ✅ Updates `is_open = false` and `is_closed_automatically = true`

**Typical usage**: Run in daily cron job
```bash
# In crontab
0 2 * * * cd /path/to/project && php artisan events:autoclose
```

### 2. `events:fix-data`
**Purpose**: Analyzes and fixes events with data problems

```bash
# View problems without fixing
php artisan events:fix-data --dry-run

# Fix problems
php artisan events:fix-data

# Analyze specific user
php artisan events:fix-data --user=123 --dry-run

# Analyze date range
php artisan events:fix-data --from=2023-01-01 --to=2023-12-31
```

**Problems it fixes**:
- ✅ Events without end date (`end = null`)
- ✅ Events without type (`event_type_id = null`)
- ✅ Events with `start > end`
- ✅ Events with anomalous durations

**Options**:
- `--dry-run`: Only analyze, don't apply changes
- `--user=ID`: Analyze specific user
- `--from=DATE`: Start date for analysis
- `--to=DATE`: End date for analysis

### 3. `events:update-extra-hours`
**Purpose**: Updates the overtime logic for existing events

```bash
# Preview changes
php artisan events:update-extra-hours --dry-run

# Apply changes
php artisan events:update-extra-hours

# Update specific team
php artisan events:update-extra-hours --team=1

# Update date range
php artisan events:update-extra-hours --from=2023-01-01 --to=2023-12-31
```

**New Logic**:
- ✅ Only events with `workday` type are NOT overtime (`is_extra_hours = false`)
- ✅ All other event types are overtime (`is_extra_hours = true`)
- ✅ Respects manual overrides (`manually_set_extra_hours = true`)

**Statistics provided**:
- 📊 Total events processed
- 📊 Events changed from overtime to regular
- 📊 Events changed from regular to overtime
- 📊 Events with manual overrides (unchanged)

### 4. `events:verify-and-fix`
**Purpose**: Comprehensive verification and correction of event data

```bash
# Complete analysis
php artisan events:verify-and-fix --dry-run

# Apply all fixes
php artisan events:verify-and-fix

# Focus on specific issues
php artisan events:verify-and-fix --check=descriptions --dry-run
php artisan events:verify-and-fix --check=extra-hours --dry-run
```

**Verifications performed**:
- ✅ Events without description
- ✅ Overtime logic consistency
- ✅ Data integrity
- ✅ Temporal consistency

**Available checks**:
- `descriptions`: Events without description
- `extra-hours`: Overtime logic verification
- `integrity`: General data integrity
- `temporal`: Date and time consistency

## 🔧 Command Usage Examples

### Daily Maintenance
```bash
#!/bin/bash
# daily-maintenance.sh

# Close expired events
php artisan events:autoclose

# Verify and fix data issues
php artisan events:verify-and-fix --dry-run

# Clean caches
php artisan cache:clear
php artisan view:clear
```

### Data Migration
```bash
#!/bin/bash
# data-migration.sh

# Backup database first
mysqldump database_name > backup_$(date +%Y%m%d).sql

# Apply new overtime logic
php artisan events:update-extra-hours --dry-run
php artisan events:update-extra-hours

# Fix any remaining issues
php artisan events:fix-data --dry-run
php artisan events:fix-data

# Verify everything is correct
php artisan events:verify-and-fix --dry-run
```

### Weekly Data Audit
```bash
#!/bin/bash
# weekly-audit.sh

# Analyze last week's data
LAST_WEEK=$(date -d "7 days ago" +%Y-%m-%d)
TODAY=$(date +%Y-%m-%d)

php artisan events:fix-data --from=$LAST_WEEK --to=$TODAY --dry-run
php artisan events:verify-and-fix --from=$LAST_WEEK --to=$TODAY --dry-run
```

## 📊 Output Examples

### Successful Execution
```bash
$ php artisan events:update-extra-hours --dry-run

🔍 Updating overtime logic for events (DRY RUN)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 Statistics:
├─ Total events processed: 1,234
├─ Changed to regular time: 456 events
├─ Changed to overtime: 789 events
└─ Manual overrides respected: 12 events

✅ Analysis completed successfully
💡 Run without --dry-run to apply changes
```

### Error Handling
```bash
$ php artisan events:fix-data --user=999

❌ Error: User with ID 999 not found
💡 Use --dry-run to preview available users
```

## ⚠️ Important Security Considerations

### Before Running Commands
1. **Always backup your database first**
2. **Use --dry-run to preview changes**
3. **Test on staging environment**
4. **Have a rollback plan ready**

### Production Environment
```bash
# Safe production workflow
php artisan down --message="Maintenance in progress"
mysqldump database > backup_$(date +%Y%m%d_%H%M%S).sql
php artisan events:verify-and-fix --dry-run
php artisan events:verify-and-fix
php artisan up
```

### Monitoring and Logs
- All commands write to Laravel logs
- Monitor `/storage/logs/laravel.log` for results
- Set up alerts for command failures

## 🔄 Scheduled Execution

### Recommended Cron Configuration
```bash
# /etc/crontab or user crontab

# Daily event cleanup at 2 AM
0 2 * * * cd /path/to/cth && php artisan events:autoclose >> /var/log/cth/autoclose.log 2>&1

# Weekly data verification on Sundays at 3 AM
0 3 * * 0 cd /path/to/cth && php artisan events:verify-and-fix --dry-run >> /var/log/cth/weekly-check.log 2>&1

# Monthly comprehensive check on 1st of month at 4 AM
0 4 1 * * cd /path/to/cth && php artisan events:fix-data --dry-run >> /var/log/cth/monthly-audit.log 2>&1
```

### Laravel Scheduler (Alternative)
Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('events:autoclose')
             ->daily()
             ->at('02:00');
             
    $schedule->command('events:verify-and-fix --dry-run')
             ->weekly()
             ->sundays()
             ->at('03:00');
}
```

---

*Last updated: November 6, 2025*