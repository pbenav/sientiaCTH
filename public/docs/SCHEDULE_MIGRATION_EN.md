# Work Schedule Day Format Migration

## Overview

This document describes an important data migration that affects work schedule configurations in the CTH application. If you are installing or upgrading CTH, you need to be aware of this change.

## The Issue

### Previous Format (Before Migration)
Work schedules used Spanish day abbreviations:
- `L` - Lunes (Monday)
- `M` - Martes (Tuesday)
- `X` - Miércoles (Wednesday)
- `J` - Jueves (Thursday)
- `V` - Viernes (Friday)
- `S` - Sábado (Saturday)
- `D` - Domingo (Sunday)

### Current Format (After Migration)
Work schedules now use ISO 8601 day numbers:
- `1` - Monday (Lunes)
- `2` - Tuesday (Martes)
- `3` - Wednesday (Miércoles)
- `4` - Thursday (Jueves)
- `5` - Friday (Viernes)
- `6` - Saturday (Sábado)
- `7` - Sunday (Domingo)

## Why This Change?

1. **International Compatibility**: ISO 8601 is a globally recognized standard
2. **Multi-language Support**: Numbers are language-independent
3. **Easier Maintenance**: Simpler to parse and validate
4. **Better Localization**: Supports future translations to other languages

## Impact on Fresh Installations

✅ **No action needed** - Fresh installations automatically use the new format.

## Impact on Existing Installations

⚠️ **Migration required** - Existing installations with work schedules configured need to run a migration command.

### Migration Steps

1. **Backup your database** before running any migration:
   ```bash
   php artisan db:backup  # If you have backup configured
   # OR manually backup your database
   ```

2. **Run the migration command**:
   ```bash
   php artisan schedule:migrate-to-iso
   ```

3. **Verify the migration**:
   The command will display a summary showing:
   - Total schedules processed
   - Successfully migrated schedules
   - Schedules already in ISO format (skipped)
   - Any errors encountered

### Migration Command Details

The `schedule:migrate-to-iso` command:
- ✅ Is **idempotent** - safe to run multiple times
- ✅ Validates all data before making changes
- ✅ Preserves existing schedule configurations
- ✅ Automatically detects and handles mixed formats
- ✅ Provides detailed progress and error reporting

### What Gets Migrated

The command updates the `user_metas` table where:
- `meta_key` = `'work_schedule'`
- `meta_value` contains the schedule JSON with day arrays

### Example Migration

**Before:**
```json
[
  {
    "days": ["L", "M", "X", "J", "V"],
    "start": "09:00",
    "end": "17:00"
  }
]
```

**After:**
```json
[
  {
    "days": [1, 2, 3, 4, 5],
    "start": "09:00",
    "end": "17:00"
  }
]
```

## Related Code Updates

This migration also required updates to several application components:

### Files Modified
- `app/Traits/Stats/CalculatesScheduledData.php` - Statistics calculations
- `app/Traits/Stats/CalculatesDashboardData.php` - Dashboard metrics
- `app/Services/SmartClockInService.php` - Clock-in/out functionality

These files now work exclusively with ISO day numbers and have been updated to ensure correct calculations.

## Troubleshooting

### Migration Errors

If the migration command reports errors:

1. **Check database connectivity**:
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

2. **Verify JSON format**:
   - Ensure `work_schedule` meta values are valid JSON
   - Corrupted JSON will be skipped with an error message

3. **Review error logs**:
   - Check Laravel logs for detailed error information
   - Path: `storage/logs/laravel.log`

### Statistics Not Calculating Correctly

If after migration statistics show incorrect values:

1. **Verify migration completion**:
   ```bash
   php artisan schedule:migrate-to-iso
   ```
   Should show `0 migrated` and all as "already in ISO format"

2. **Check schedules in database**:
   ```sql
   SELECT user_id, meta_value 
   FROM user_metas 
   WHERE meta_key = 'work_schedule';
   ```
   The `days` arrays should contain numbers 1-7, not letters

3. **Clear application cache**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

## Version Information

- **Migration introduced**: Version 2.x (November 2024)
- **Migration command**: `schedule:migrate-to-iso`
- **Related commit**: `f898c6e0` and `fed64cea`

## Support

If you encounter issues with this migration:
1. Check this documentation first
2. Review the migration command output
3. Check Laravel logs for errors
4. Ensure all code is up-to-date from the repository

## See Also

- [Developer Manual](DEVELOPER_MANUAL_EN.md) - Complete development guide
- [User Manual](USER_MANUAL_EN.md) - User-facing documentation
- Migration command source: `app/Console/Commands/MigrateWorkScheduleDaysToISO.php`
