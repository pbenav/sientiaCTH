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
        Schema::table('event_types', function (Blueprint $table) {
            if (!Schema::hasColumn('event_types', 'is_break_type')) {
                $table->boolean('is_break_type')->default(false)->after('is_workday_type');
            }
        });
        
        // Update existing "Pausa" event types to be break types
        DB::table('event_types')
            ->where('name', 'Pausa')
            ->update(['is_break_type' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            if (Schema::hasColumn('event_types', 'is_break_type')) {
                $table->dropColumn('is_break_type');
            }
        });
    }
};
