<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Team Announcements Table Migration
 * 
 * Team-wide announcements and notices.
 * Depends on: teams, users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('team_announcements')) {
            Schema::create('team_announcements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('content');
                $table->string('format')->default('html');
                $table->boolean('is_active')->default(true);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->index(['team_id', 'is_active']);
            });
        }

        // Create Welcome announcement
        $this->ensureWelcomeAnnouncement();
    }

    private function ensureWelcomeAnnouncement(): void
    {
        if (DB::table('team_announcements')->where('title', '¡Bienvenido a sientiaCTH!')->doesntExist()) {
            DB::table('team_announcements')->insert([
                'team_id' => 1,
                'title' => '¡Bienvenido a sientiaCTH!',
                'content' => '<h2>¡Hola! Bienvenido a sientiaCTH (Control de Tiempo y Horarios)</h2>
                             <p>Esta aplicación ha sido diseñada para facilitar la gestión del tiempo laboral, permitiendo un registro preciso y transparente de la jornada de trabajo.</p>
                             <p><strong>¿Qué es sientiaCTH?</strong> Es una solución integral que combina una plataforma web avanzada con una aplicación móvil intuitiva, permitiendo fichajes mediante SmartClockIn, gestión de pausas, vacaciones y mucho más.</p>
                             <p><strong>¿Quién ha hecho esto?</strong> Este sistema ha sido desarrollado íntegramente por <strong>Sientia</strong>, con el objetivo de modernizar y simplificar el control horario empresarial.</p>
                             <p>Esperamos que esta herramienta te sea de gran utilidad. ¡Empecemos a trabajar!</p>',
                'format' => 'html',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_announcements');
    }
};
