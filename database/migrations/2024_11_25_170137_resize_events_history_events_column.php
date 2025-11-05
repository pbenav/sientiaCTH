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
        Schema::table('events_history', function (Blueprint $table) {
            // Modify the original_event field size to 2048 characters
            $table->string('original_event', 2048)->change();
            $table->string('modified_event', 2048)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events_history', function (Blueprint $table) {
            // Revert the original_event field size to its previous configuration (255 by default)
            $table->string('original_event')->change();
            $table->string('modified_event')->change();
        });
    }
};
