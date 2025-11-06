<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team;
use App\Models\EventType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Add new "Pause" event type to all teams
            $teams = Team::all();
            
            foreach ($teams as $team) {
                // Check if pause event type already exists
                $existingPauseType = $team->eventTypes()
                    ->where('name', 'Pausa')
                    ->orWhere('name', 'Pause')
                    ->first();
                
                if (!$existingPauseType) {
                    EventType::create([
                        'team_id' => $team->id,
                        'name' => 'Pausa',
                        'color' => '#FFA500', // Orange color for pause
                        'is_workday_type' => false, // Pause is not workday time
                        'requires_location' => false,
                        'requires_description' => false,
                        'is_break_type' => true, // Mark as break type
                        'max_duration_minutes' => null, // No max duration limit
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    echo "✅ Added 'Pausa' event type to team: {$team->name}\n";
                } else {
                    echo "ℹ️  'Pausa' event type already exists for team: {$team->name}\n";
                }
            }
            
            echo "🎉 Migration completed successfully!\n";
            
        } catch (\Exception $e) {
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Remove "Pause" event types from all teams
            EventType::where('name', 'Pausa')
                ->orWhere('name', 'Pause')
                ->delete();
                
            echo "🔄 Pause event types removed successfully!\n";
            
        } catch (\Exception $e) {
            echo "❌ Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
};
