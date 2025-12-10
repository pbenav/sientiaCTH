<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add event retention period configuration to teams.
     * Default is 5 years (60 months) for historical record keeping.
     */
    public function up(): void
    {
        // Check if table exists and column doesn't already exist
        if (!Schema::hasTable('teams')) {
            return;
        }

        if (!Schema::hasColumn('teams', 'event_retention_months')) {
            Schema::table('teams', function (Blueprint $table) {
                // Retention period in months (default 60 = 5 years)
                $table->integer('event_retention_months')->default(60)->after('personal_team');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('teams')) {
            return;
        }

        if (Schema::hasColumn('teams', 'event_retention_months')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('event_retention_months');
            });
        }
    }
};
