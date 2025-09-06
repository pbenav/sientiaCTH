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
        Schema::table('event_types', function (Blueprint $table) {
            $table->boolean('is_all_day')->default(false)->after('observations');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_authorized')->default(false)->after('is_open');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn('is_all_day');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_authorized');
        });
    }
};
