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
        Schema::table('event_types', function (Blueprint $table) {
            if (!Schema::hasColumn('event_types', 'is_authorizable')) {
                $table->boolean('is_authorizable')->default(false)->after('is_workday_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            if (Schema::hasColumn('event_types', 'is_authorizable')) {
                $table->dropColumn('is_authorizable');
            }
        });
    }
};
