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
            // Modificar el tamaño del campo original_event a 2048 caracteres
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
            // Revertir el tamaño del campo original_event a su configuración anterior (255 por defecto)
            $table->string('original_event')->change();
            $table->string('modified_event')->change();
        });
    }
};
