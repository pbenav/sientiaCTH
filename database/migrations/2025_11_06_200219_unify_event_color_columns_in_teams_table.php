<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the new special_event_color column
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'special_event_color')) {
                $table->string('special_event_color', 7)->nullable()->after('exceptional_event_color');
            }
        });

        // Copy data from exceptional_event_color to special_event_color
        // If exceptional_event_color exists, use it; otherwise use irregular_event_color
        DB::statement('
            UPDATE teams 
            SET special_event_color = COALESCE(exceptional_event_color, irregular_event_color)
            WHERE special_event_color IS NULL
        ');

        // Remove the old columns after data migration
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'irregular_event_color')) {
                $table->dropColumn('irregular_event_color');
            }
            if (Schema::hasColumn('teams', 'exceptional_event_color')) {
                $table->dropColumn('exceptional_event_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the original columns
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'irregular_event_color')) {
                $table->string('irregular_event_color', 7)->nullable();
            }
            if (!Schema::hasColumn('teams', 'exceptional_event_color')) {
                $table->string('exceptional_event_color', 7)->nullable();
            }
        });

        // Copy data back from special_event_color to both columns
        DB::statement('
            UPDATE teams 
            SET exceptional_event_color = special_event_color,
                irregular_event_color = special_event_color
            WHERE special_event_color IS NOT NULL
        ');

        // Drop the unified column
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'special_event_color')) {
                $table->dropColumn('special_event_color');
            }
        });
    }
};
