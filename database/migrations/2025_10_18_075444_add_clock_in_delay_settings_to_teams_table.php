<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'force_clock_in_delay')) {
                $table->boolean('force_clock_in_delay')->default(false)->after('personal_team');
            }
            if (!Schema::hasColumn('teams', 'clock_in_delay_minutes')) {
                $table->unsignedInteger('clock_in_delay_minutes')->nullable()->after('force_clock_in_delay');
            }
            if (!Schema::hasColumn('teams', 'clock_in_grace_period_minutes')) {
                $table->unsignedInteger('clock_in_grace_period_minutes')->nullable()->after('clock_in_delay_minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $columnsToRemove = [];
            
            if (Schema::hasColumn('teams', 'force_clock_in_delay')) {
                $columnsToRemove[] = 'force_clock_in_delay';
            }
            if (Schema::hasColumn('teams', 'clock_in_delay_minutes')) {
                $columnsToRemove[] = 'clock_in_delay_minutes';
            }
            if (Schema::hasColumn('teams', 'clock_in_grace_period_minutes')) {
                $columnsToRemove[] = 'clock_in_grace_period_minutes';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
