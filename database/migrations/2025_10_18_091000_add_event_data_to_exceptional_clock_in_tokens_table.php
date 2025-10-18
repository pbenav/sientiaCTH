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
        Schema::table('exceptional_clock_in_tokens', function (Blueprint $table) {
            $table->json('event_data')->nullable()->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exceptional_clock_in_tokens', function (Blueprint $table) {
            $table->dropColumn('event_data');
        });
    }
};
