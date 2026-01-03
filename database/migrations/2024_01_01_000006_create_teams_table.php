<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Teams Table Migration
 * 
 * Creates and maintains the teams table.
 * Fully idempotent and preserves existing data.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->index();
                $table->string('name');
                $table->string('pdf_engine')->default('browsershot');
                $table->integer('max_report_months')->default(3)->nullable();
                $table->integer('async_report_threshold_months')->nullable();
                $table->boolean('personal_team');
                $table->integer('event_retention_months')->default(60);
                $table->string('timezone')->nullable();
                $table->unsignedInteger('event_expiration_days')->nullable();
                $table->boolean('force_clock_in_delay')->default(false);
                $table->unsignedInteger('clock_in_delay_minutes')->nullable();
                $table->unsignedInteger('clock_in_grace_period_minutes')->nullable();
                $table->string('special_event_color', 7)->nullable();
                $table->unsignedInteger('max_member_teams')->default(5)
                    ->comment('Maximum number of teams that members of this team can create');
                $table->timestamps();
            });
        } else {
            $this->addMissingColumns();
        }

        // Ensure Welcome team exists
        $this->ensureWelcomeTeam();
    }

    private function addMissingColumns(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'pdf_engine')) {
                $table->string('pdf_engine')->default('browsershot')->after('name');
            }
            if (!Schema::hasColumn('teams', 'max_report_months')) {
                $table->integer('max_report_months')->default(3)->nullable()->after('pdf_engine');
            }
            if (!Schema::hasColumn('teams', 'async_report_threshold_months')) {
                $table->integer('async_report_threshold_months')->nullable()->after('max_report_months');
            }
            if (!Schema::hasColumn('teams', 'event_retention_months')) {
                $table->integer('event_retention_months')->default(60)->after('personal_team');
            }
            if (!Schema::hasColumn('teams', 'timezone')) {
                $table->string('timezone')->nullable()->after('event_retention_months');
            }
            if (!Schema::hasColumn('teams', 'event_expiration_days')) {
                $table->unsignedInteger('event_expiration_days')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('teams', 'force_clock_in_delay')) {
                $table->boolean('force_clock_in_delay')->default(false)->after('event_expiration_days');
            }
            if (!Schema::hasColumn('teams', 'clock_in_delay_minutes')) {
                $table->unsignedInteger('clock_in_delay_minutes')->nullable()->after('force_clock_in_delay');
            }
            if (!Schema::hasColumn('teams', 'clock_in_grace_period_minutes')) {
                $table->unsignedInteger('clock_in_grace_period_minutes')->nullable()->after('clock_in_delay_minutes');
            }
            if (!Schema::hasColumn('teams', 'special_event_color')) {
                $table->string('special_event_color', 7)->nullable()->after('clock_in_grace_period_minutes');
            }
            if (!Schema::hasColumn('teams', 'max_member_teams')) {
                $table->unsignedInteger('max_member_teams')->default(5)
                    ->comment('Maximum number of teams that members of this team can create')
                    ->after('special_event_color');
            }
        });
    }

    private function ensureWelcomeTeam(): void
    {
        if (DB::table('teams')->where('id', 1)->doesntExist()) {
            DB::table('teams')->insert([
                'id' => 1,
                'user_id' => 1,
                'name' => 'Bienvenida',
                'personal_team' => false,
                'max_member_teams' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
