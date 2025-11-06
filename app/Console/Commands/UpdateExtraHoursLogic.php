<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExtraHoursLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-extra-hours {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing events with new extra hours logic - only main workday events are NOT overtime';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Updating events with new extra hours logic...');
        $this->info('New rule: Only main workday event types (is_workday_type = true) are NOT overtime');
        
        // Get all events with their event types
        $events = Event::with('eventType')->get();
        
        $updated = 0;
        $total = $events->count();
        
        foreach ($events as $event) {
            // Calculate new is_extra_hours value
            $newIsExtraHours = !($event->eventType && $event->eventType->is_workday_type);
            
            // Only update if value changed
            if ($event->is_extra_hours !== $newIsExtraHours) {
                if (!$dryRun) {
                    $event->update(['is_extra_hours' => $newIsExtraHours]);
                }
                
                $updated++;
                
                $this->line(sprintf(
                    'Event #%d (%s): %s -> %s',
                    $event->id,
                    $event->eventType ? $event->eventType->name : 'No Type',
                    $event->is_extra_hours ? 'EXTRA' : 'NORMAL',
                    $newIsExtraHours ? 'EXTRA' : 'NORMAL'
                ));
            }
        }
        
        $this->info("Processed {$total} events");
        $this->info("Updated {$updated} events");
        
        if ($dryRun) {
            $this->warn('This was a DRY RUN - run without --dry-run to apply changes');
        } else {
            $this->info('✅ Update completed successfully!');
        }
        
        return 0;
    }
}
