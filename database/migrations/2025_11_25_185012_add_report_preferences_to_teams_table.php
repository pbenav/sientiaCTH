<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->integer('max_report_months')->nullable()->default(3)->after('chrome_path');
            $table->integer('async_report_threshold_months')->nullable()->after('max_report_months');
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
            $table->dropColumn(['max_report_months', 'async_report_threshold_months']);
        });
    }
};
