<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('force_max_workday_duration')->default(false)->after('clock_in_grace_period_minutes');
            $table->integer('max_workday_duration_minutes')->nullable()->after('force_max_workday_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['force_max_workday_duration', 'max_workday_duration_minutes']);
        });
    }
};
