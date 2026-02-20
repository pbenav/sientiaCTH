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
        if (Schema::hasTable('events_history')) {
            Schema::table('events_history', function (Blueprint $table) {
                if (Schema::hasColumn('events_history', 'original_event')) {
                    $table->longText('original_event')->change();
                }
                if (Schema::hasColumn('events_history', 'modified_event')) {
                    $table->longText('modified_event')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events_history', function (Blueprint $table) {
            $table->string('original_event', 2048)->change();
            $table->string('modified_event', 2048)->change();
        });
    }
};
