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
        Schema::table('events_history', function (Blueprint $table) {
            $table->longText('original_event')->change();
            $table->longText('modified_event')->change();
        });
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
