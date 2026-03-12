<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Users Table Migration
 * 
 * Creates and maintains the core users table with all necessary fields.
 * This migration is fully idempotent and preserves existing data.
 * 
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('user_code')->nullable()->index();
                $table->string('name');
                $table->string('family_name1')->nullable();
                $table->string('family_name2')->nullable();
                $table->string('email')->unique();
                $table->boolean('is_admin')->default(false)->index();
                $table->tinyInteger('week_starts_on')->default(1);
                $table->enum('vacation_calculation_type', ['natural', 'working'])
                    ->default('natural')
                    ->comment('Type of vacation calculation: natural (calendar days) or working (excluding weekends/holidays)');
                $table->unsignedInteger('vacation_working_days')->default(22)
                    ->comment('Number of working days for vacation calculation when type is "working"');
                $table->boolean('geolocation_enabled')->default(false);
                $table->boolean('notify_new_messages')->default(true);
                $table->string('locale', 5)->default('es')->comment('User preferred language (es, en)');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('two_factor_confirmed_at')->nullable();
                $table->rememberToken();
                $table->foreignId('current_team_id')->nullable();
                $table->string('profile_photo_path', 2048)->nullable();
                $table->timestamps();

                $table->index('email_verified_at');
            });
        } else {
            // Verify and add missing columns
            $this->addMissingColumns();
        }

        // Ensure default admin user exists
        $this->ensureAdminUser();
    }

    /**
     * Add any missing columns to existing users table
     */
    private function addMissingColumns(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_code')) {
                $table->string('user_code')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('users', 'family_name1')) {
                $table->string('family_name1')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'family_name2')) {
                $table->string('family_name2')->nullable()->after('family_name1');
            }
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->index()->after('email');
            }
            if (!Schema::hasColumn('users', 'week_starts_on')) {
                $table->tinyInteger('week_starts_on')->default(1)->after('is_admin');
            }
            if (!Schema::hasColumn('users', 'vacation_calculation_type')) {
                $table->enum('vacation_calculation_type', ['natural', 'working'])
                    ->default('natural')
                    ->comment('Type of vacation calculation: natural (calendar days) or working (excluding weekends/holidays)')
                    ->after('week_starts_on');
            }
            if (!Schema::hasColumn('users', 'vacation_working_days')) {
                $table->unsignedInteger('vacation_working_days')->default(22)
                    ->comment('Number of working days for vacation calculation when type is "working"')
                    ->after('vacation_calculation_type');
            }
            if (!Schema::hasColumn('users', 'geolocation_enabled')) {
                $table->boolean('geolocation_enabled')->default(false)->after('vacation_working_days');
            }
            if (!Schema::hasColumn('users', 'notify_new_messages')) {
                $table->boolean('notify_new_messages')->default(true)->after('geolocation_enabled');
            }
            if (!Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 5)->default('es')
                    ->comment('User preferred language (es, en)')
                    ->after('notify_new_messages');
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
            if (!Schema::hasColumn('users', 'current_team_id')) {
                $table->foreignId('current_team_id')->nullable()->after('two_factor_confirmed_at');
            }
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path', 2048)->nullable()->after('current_team_id');
            }
        });
    }

    /**
     * Ensure default admin user exists
     */
    private function ensureAdminUser(): void
    {
        if (DB::table('users')->where('id', 1)->doesntExist()) {
            DB::table('users')->insert([
                'id' => 1,
                'user_code' => 'ADMIN',
                'name' => 'Administrador',
                'email' => 'admin@sientiaCTH.local',
                'is_admin' => true,
                'locale' => 'es',
                'email_verified_at' => now(),
                'password' => \Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
