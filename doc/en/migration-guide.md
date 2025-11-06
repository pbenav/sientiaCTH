# Migration Guide

This document provides comprehensive guidelines for deploying database migrations and system updates in production environments.

## 🚀 Production Deployment Overview

The CTH application includes several critical migrations that update data structures and business logic. All migrations are designed with robust error handling and rollback capabilities.

## 📦 Available Migrations (November 2025)

### Migration Set: Event System Improvements

#### 1. `2025_11_06_162621_update_workday_types_for_all_teams.php`
**Purpose**: Updates workday event types configuration for all teams
- ✅ Sets `is_workday = true` for workday event types
- ✅ Comprehensive error handling and logging
- ✅ Idempotent operations (safe to run multiple times)

#### 2. `2025_11_06_163036_update_existing_events_extra_hours_logic.php`
**Purpose**: Applies new overtime logic to existing events
- ✅ Updates `is_extra_hours` based on event type
- ✅ Only workday events are NOT overtime
- ✅ Respects manual overrides
- ✅ Batch processing for performance

#### 3. `2025_11_06_163513_ensure_is_extra_hours_default_values.php`
**Purpose**: Ensures all events have proper overtime status
- ✅ Sets default values for null `is_extra_hours`
- ✅ Applies business logic consistently
- ✅ Handles edge cases and legacy data

#### 4. `2025_11_06_163626_fix_events_without_description.php`
**Purpose**: Fixes events with missing descriptions
- ✅ Uses event type name as default description
- ✅ Preserves existing descriptions
- ✅ Improves data consistency

## ⚠️ Pre-Deployment Checklist

### 1. System Backup
```bash
# Complete database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Application files backup
tar -czf app_backup_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/cth/

# Verify backup integrity
mysql -u username -p -e "SELECT COUNT(*) FROM events;" database_name
```

### 2. Environment Verification
```bash
# Check PHP version
php -v

# Verify Laravel installation
php artisan --version

# Check database connection
php artisan migrate:status

# Verify disk space
df -h

# Check memory availability
free -m
```

### 3. Dependencies Update
```bash
# Update Composer dependencies
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## 🔧 Deployment Process

### Step 1: Maintenance Mode
```bash
# Enable maintenance mode
php artisan down --message="System update in progress" --retry=60
```

### Step 2: Code Deployment
```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Build frontend assets
npm run build
```

### Step 3: Migration Execution
```bash
# Run migrations with verbose output
php artisan migrate --force --verbose

# Verify migration status
php artisan migrate:status
```

### Step 4: Data Verification
```bash
# Verify data integrity
php artisan events:verify-and-fix --dry-run

# Check overtime logic
php artisan events:update-extra-hours --dry-run

# Validate descriptions
grep -c "description IS NOT NULL" /var/log/migration.log
```

### Step 5: Cache Optimization
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
```

### Step 6: Disable Maintenance Mode
```bash
# Exit maintenance mode
php artisan up
```

## 🛡️ Safety Measures

### Error Handling
All migrations include:
- **Try-catch blocks** for error handling
- **Detailed logging** of all operations
- **Rollback procedures** for failed operations
- **Progress indicators** for long-running processes

### Rollback Procedures
```bash
# Rollback last migration batch
php artisan migrate:rollback

# Rollback specific number of batches
php artisan migrate:rollback --step=1

# Rollback to specific migration
php artisan migrate:rollback --to=2025_11_06_162621
```

### Data Integrity Checks
```bash
# Before migration
php artisan events:verify-and-fix --dry-run > pre_migration_check.log

# After migration
php artisan events:verify-and-fix --dry-run > post_migration_check.log

# Compare results
diff pre_migration_check.log post_migration_check.log
```

## 📊 Monitoring and Validation

### Key Metrics to Monitor
```sql
-- Check event type distribution
SELECT event_type_id, COUNT(*) as count 
FROM events 
GROUP BY event_type_id;

-- Verify overtime logic
SELECT is_extra_hours, COUNT(*) as count 
FROM events 
GROUP BY is_extra_hours;

-- Check descriptions
SELECT 
  COUNT(*) as total_events,
  COUNT(description) as events_with_description,
  COUNT(*) - COUNT(description) as events_without_description
FROM events;
```

### Performance Monitoring
```bash
# Monitor system resources during migration
top -p $(pgrep -f "artisan migrate")

# Check database performance
mysqladmin -u username -p processlist

# Monitor log files
tail -f storage/logs/laravel.log
```

## 🔄 Post-Deployment Tasks

### 1. Verification Commands
```bash
# Run comprehensive verification
php artisan events:verify-and-fix --dry-run

# Update overtime calculations
php artisan events:update-extra-hours --dry-run

# Fix any remaining data issues
php artisan events:fix-data --dry-run
```

### 2. User Communication
- Notify users that maintenance is complete
- Communicate any new features or changes
- Provide support contacts for issues

### 3. Monitoring Setup
```bash
# Set up log monitoring
tail -f storage/logs/laravel.log | grep -E "(ERROR|CRITICAL)"

# Monitor application performance
php artisan horizon:status  # if using queues
```

## 🚨 Emergency Procedures

### If Migration Fails
1. **Don't panic** - migrations are designed to be recoverable
2. **Check logs** for specific error messages
3. **Restore from backup** if necessary
4. **Contact support** with error details

### Rollback Process
```bash
# Stop application
php artisan down

# Restore from backup
mysql -u username -p database_name < backup_file.sql

# Reset migration state if needed
php artisan migrate:reset
php artisan migrate

# Restart application
php artisan up
```

### Critical Error Response
```bash
# Enable debug mode temporarily
# Set APP_DEBUG=true in .env (production should be false)

# Check detailed error logs
tail -100 storage/logs/laravel.log

# Verify database connectivity
php artisan migrate:status

# Test basic functionality
php artisan tinker
```

## 📈 Performance Considerations

### Large Dataset Optimization
- Migrations process data in batches (1000 records default)
- Memory usage is optimized to prevent timeouts
- Progress indicators show completion status

### Estimated Migration Times
Based on dataset size:
- **< 10,000 events**: 1-2 minutes
- **10,000 - 100,000 events**: 5-15 minutes
- **> 100,000 events**: 15+ minutes

### Resource Requirements
- **Memory**: Minimum 512MB, recommended 1GB+
- **Disk Space**: 20% free space recommended
- **CPU**: Moderate usage during batch processing

## 📋 Migration Checklist

### Pre-Migration
- [ ] System backup completed
- [ ] Dependencies updated
- [ ] Maintenance mode enabled
- [ ] Resources verified
- [ ] Team notified

### During Migration
- [ ] Monitor progress logs
- [ ] Watch system resources
- [ ] Keep backup readily available
- [ ] Document any issues

### Post-Migration
- [ ] Verify data integrity
- [ ] Run verification commands
- [ ] Test critical functionality
- [ ] Monitor for errors
- [ ] Disable maintenance mode
- [ ] Notify users of completion

## 📞 Support and Troubleshooting

### Common Issues
1. **Memory exhaustion**: Increase PHP memory limit
2. **Timeout errors**: Increase max_execution_time
3. **Lock wait timeout**: Check for long-running queries
4. **Permission errors**: Verify file/database permissions

### Getting Help
- Check application logs: `storage/logs/laravel.log`
- Use diagnostic commands: `php artisan events:verify-and-fix --dry-run`
- Review migration files for specific error handling
- Contact system administrator with error details

---

*Last updated: November 6, 2025*