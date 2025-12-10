<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a Welcome team where all new users will be assigned.
     */
    public function up(): void
    {
        // Check if teams table exists
        if (!Schema::hasTable('teams')) {
            return;
        }

        // Verify admin user exists before creating team
        $adminExists = DB::table('users')->where('id', 1)->exists();
        
        if (!$adminExists) {
            // If admin doesn't exist, try to find any admin user
            $adminUser = DB::table('users')->where('is_admin', true)->first();
            
            if (!$adminUser) {
                // No admin found, skip team creation
                return;
            }
            
            $adminId = $adminUser->id;
        } else {
            $adminId = 1;
        }

        // Create the Welcome team if it doesn't exist
        $welcomeTeamExists = DB::table('teams')
            ->where('name', 'Bienvenida')
            ->exists();

        if (!$welcomeTeamExists) {
            $teamData = [
                'user_id' => $adminId,
                'name' => 'Bienvenida',
                'personal_team' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add event_retention_months if column exists
            if (Schema::hasColumn('teams', 'event_retention_months')) {
                $teamData['event_retention_months'] = 60;
            }
            
            DB::table('teams')->insert($teamData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the Welcome team
        // Only if it has no members to avoid data loss
        if (Schema::hasTable('teams')) {
            $welcomeTeam = DB::table('teams')->where('name', 'Bienvenida')->first();
            
            if ($welcomeTeam && Schema::hasTable('team_user')) {
                $memberCount = DB::table('team_user')
                    ->where('team_id', $welcomeTeam->id)
                    ->count();
                
                // Only delete if no members are assigned
                if ($memberCount === 0) {
                    DB::table('teams')->where('name', 'Bienvenida')->delete();
                }
            }
        }
    }
};
