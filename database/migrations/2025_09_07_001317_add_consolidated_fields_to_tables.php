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
        // Add columns to 'events' table
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'event_type_id')) {
                $table->foreignId('event_type_id')->nullable()->after('description')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('events', 'is_authorized')) {
                $table->boolean('is_authorized')->default(false)->after('is_open');
            }
        });

        // Add column to 'event_types' table
        Schema::table('event_types', function (Blueprint $table) {
            if (!Schema::hasColumn('event_types', 'is_all_day')) {
                $table->boolean('is_all_day')->default(false)->after('color');
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
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'event_type_id')) {
                $table->dropForeign(['event_type_id']);
                $table->dropColumn('event_type_id');
            }
            if (Schema::hasColumn('events', 'is_authorized')) {
                $table->dropColumn('is_authorized');
            }
        });

        Schema::table('event_types', function (Blueprint $table) {
            if (Schema::hasColumn('event_types', 'is_all_day')) {
                $table->dropColumn('is_all_day');
            }
        });
    }
};
