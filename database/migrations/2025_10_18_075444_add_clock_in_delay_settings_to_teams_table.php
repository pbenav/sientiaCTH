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
            $table->boolean('force_clock_in_delay')->default(false)->after('personal_team');
            $table->unsignedInteger('clock_in_delay_minutes')->nullable()->after('force_clock_in_delay');
            $table->unsignedInteger('clock_in_grace_period_minutes')->nullable()->after('clock_in_delay_minutes');
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
            $table->dropColumn([
                'force_clock_in_delay',
                'clock_in_delay_minutes',
                'clock_in_grace_period_minutes',
            ]);
        });
    }
};
