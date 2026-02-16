<?php

declare(strict_types=1);

use App\Support\Permissions\PermissionMatrix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Initial Database Schema for CTH v1.0
 * 
 * This migration consolidates all previous migrations into a single, optimized schema.
 * It includes all core tables, optimized indexes for performance, and proper foreign key constraints.
 * It is designed to be fully idempotent and handles incremental updates for existing databases.
 * 
 * @version 1.0.2
 * @since 2026-01-02
 * @updated 2026-01-03
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Users table - Core user authentication and profile
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

                // Indexes for performance
                $table->index('email_verified_at');
            });
        } else {
            // Add missing columns to existing users table
            if (!Schema::hasColumn('users', 'locale')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('locale', 5)->default('es')->after('notify_new_messages')->comment('User preferred language (es, en)');
                });
            }
        }

        // Password resets
        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Failed jobs queue
        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        // Personal access tokens (Sanctum)
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index('last_used_at');
            });
        }

        // Teams table
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
            // Add missing columns to existing teams table
            if (!Schema::hasColumn('teams', 'max_member_teams')) {
                Schema::table('teams', function (Blueprint $table) {
                    $table->unsignedInteger('max_member_teams')->default(5)->after('special_event_color')
                        ->comment('Maximum number of teams that members of this team can create');
                });
            }
        }

        // Permissions system
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->string('category', 100)->nullable();
                $table->boolean('requires_context')->default(false);
                $table->boolean('is_system')->default(false);
                $table->timestamps();

                $table->index('category');
            });
        }

        // Roles table
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['team_id', 'name']);
            });
        }

        // Team-User pivot table
        if (!Schema::hasTable('team_user')) {
            Schema::create('team_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('role')->nullable();
                $table->foreignId('custom_role_id')->nullable()->constrained('roles')->onDelete('set null');
                $table->timestamps();

                $table->unique(['team_id', 'user_id']);
            });
        } else {
            // Add custom_role_id column to existing team_user table if missing
            if (!Schema::hasColumn('team_user', 'custom_role_id')) {
                Schema::table('team_user', function (Blueprint $table) {
                    $table->foreignId('custom_role_id')->nullable()->after('role')->constrained('roles')->onDelete('set null');
                });
            }
        }

        // Team invitations (Jetstream)
        if (!Schema::hasTable('team_invitations')) {
            Schema::create('team_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('email');
                $table->string('role')->nullable();
                $table->timestamps();

                $table->unique(['team_id', 'email']);
            });
        }

        // Sessions table
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Work Centers table
        if (!Schema::hasTable('work_centers')) {
            Schema::create('work_centers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('code')->unique();
                $table->string('nfc_tag_id', 64)->nullable()->unique();
                $table->text('nfc_tag_description')->nullable();
                $table->string('nfc_payload', 500)->nullable();
                $table->timestamp('nfc_tag_generated_at')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->timestamps();

                // Performance indexes for NFC lookups
                $table->index('nfc_tag_id');
                $table->index('nfc_payload');
            });
        }

        // Event Types table
        if (!Schema::hasTable('event_types')) {
            Schema::create('event_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('observations')->nullable();
                $table->string('color')->nullable();
                $table->boolean('is_all_day')->default(false);
                $table->boolean('is_workday_type')->default(false);
                $table->boolean('is_break_type')->default(false);
                $table->boolean('is_authorizable')->default(false);
                $table->boolean('is_pause_type')->default(false);
                $table->timestamps();

                // Index for filtering workday types
                $table->index('is_workday_type');
            });
        }

        // Events table - Main time tracking table
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
                $table->dateTime('start');
                $table->dateTime('end')->nullable();
                $table->string('description')->nullable();
                $table->foreignId('event_type_id')->nullable()->constrained()->onDelete('set null');
                $table->text('observations')->nullable();
                $table->foreignId('work_center_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('authorized_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->boolean('is_open');
                $table->boolean('is_authorized')->default(false);
                $table->boolean('is_closed_automatically')->default(false);
                $table->boolean('is_exceptional')->default(false);
                $table->boolean('is_extra_hours')->default(false);
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('nfc_tag_id', 100)->nullable();
                $table->timestamps();

                // Strategic indexes for performance optimization
                // Date range queries (most common use case)
                $table->index(['user_id', 'start', 'end'], 'events_user_date_range_idx');
                $table->index(['team_id', 'start', 'end'], 'events_team_date_range_idx');
                // Event type filtering
                $table->index(['event_type_id', 'start'], 'events_type_date_idx');
                // Open events lookup
                $table->index(['user_id', 'is_open'], 'events_user_open_idx');
                // Work center tracking
                $table->index('work_center_id');
            });
        }

        // Events history table - Audit trail
        if (!Schema::hasTable('events_history')) {
            Schema::create('events_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('tablename');
                $table->string('original_event', 2048);
                $table->string('modified_event', 2048);
                $table->timestamps();

                // Index for querying user history
                $table->index(['user_id', 'created_at']);
            });
        }

        // Holidays table
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('date');
                $table->string('type')->nullable();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['date', 'team_id']);
                $table->index('date');
                $table->index('type');
            });
        }

        // User metadata table
        if (!Schema::hasTable('user_meta')) {
            Schema::create('user_meta', function (Blueprint $table) {
                $table->id();
                $table->string('meta_key');
                $table->text('meta_value')->nullable();
                $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'meta_key']);
                $table->index('user_id');
                $table->index('meta_key');
            });
        }

        // Messages table - Internal messaging system
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users');
                $table->foreignId('parent_id')->nullable()->constrained('messages')->onDelete('cascade');
                $table->string('subject');
                $table->text('body');
                $table->timestamp('sender_deleted_at')->nullable();
                $table->timestamp('sender_purged_at')->nullable();
                $table->timestamps();

                $table->index('parent_id');
            });
        } else {
            // Add missing columns to existing messages table
            if (!Schema::hasColumn('messages', 'parent_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->foreignId('parent_id')->nullable()->after('sender_id')->constrained('messages')->onDelete('cascade');
                    $table->index('parent_id');
                });
            }
        }

        // Message recipients pivot
        if (!Schema::hasTable('message_user')) {
            Schema::create('message_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
            });
        }

        // Notifications table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // Failed login attempts tracking
        if (!Schema::hasTable('failed_login_attempts')) {
            Schema::create('failed_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45);
                $table->timestamp('timestamp');
                $table->integer('lockout_time')->nullable();
                $table->timestamps();

                $table->index('ip_address');
            });
        }

        // Exceptional clock-in tokens
        if (!Schema::hasTable('exceptional_clock_in_tokens')) {
            Schema::create('exceptional_clock_in_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('token')->unique();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }

        // Team announcements
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

        // Permission-Role pivot
        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('granted_at')->useCurrent();

                $table->unique(['permission_id', 'role_id']);
            });
        }

        // User permissions (direct assignments)
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
                $table->json('context')->nullable();
                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('granted_at')->useCurrent();
                $table->foreignId('revoked_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('revoked_at')->nullable();

                $table->index(['user_id', 'valid_until']);
            });
        }

        // Permission audit log
        if (!Schema::hasTable('permission_audit_log')) {
            Schema::create('permission_audit_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('permission_name');
                $table->string('action', 50);
                $table->enum('result', ['allowed', 'denied'])->nullable();
                $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('resource_type', 100)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->json('context')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                // Indexes for audit queries
                $table->index(['user_id', 'created_at']);
                $table->index('permission_name');
                $table->index('action');
            });
        }

        // Create Welcome team
        if (\DB::table('teams')->where('id', 1)->doesntExist()) {
            \DB::table('teams')->insert([
                'id' => 1,
                'user_id' => 1, // Will be the admin user
                'name' => 'Bienvenida',
                'personal_team' => false,
                'max_member_teams' => 5, // Default limit for new users in Welcome team
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create global administrator user
        // Default password: "admin123" - MUST be changed on first login
        if (\DB::table('users')->where('id', 1)->doesntExist()) {
            \DB::table('users')->insert([
                'id' => 1,
                'user_code' => 'ADMIN',
                'name' => 'Administrador',
                'email' => 'admin@cth.local',
                'is_admin' => true,
                'current_team_id' => 1, // Assign to Welcome team
                'email_verified_at' => now(),
                'password' => \Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add admin to Welcome team
        if (\DB::table('team_user')->where('team_id', 1)->where('user_id', 1)->doesntExist()) {
            \DB::table('team_user')->insert([
                'team_id' => 1,
                'user_id' => 1,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed base permissions and system roles for the Welcome team
        PermissionMatrix::syncTeamRoles(1, 1);

        // Create default work center
        if (\DB::table('work_centers')->where('id', 1)->doesntExist()) {
            \DB::table('work_centers')->insert([
                'id' => 1,
                'team_id' => 1,
                'name' => 'Sede Central',
                'code' => 'HQ-001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create basic event types
        $eventTypes = [
            [
                'id' => 1,
                'team_id' => 1,
                'name' => 'Jornada Laboral',
                'observations' => 'Evento principal de trabajo.',
                'color' => '#10b981', // Green
                'is_all_day' => false,
                'is_workday_type' => true,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'team_id' => 1,
                'name' => 'Vacaciones',
                'observations' => 'Días de descanso anual.',
                'color' => '#3b82f6', // Blue
                'is_all_day' => true,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'team_id' => 1,
                'name' => 'Asuntos Propios',
                'observations' => 'Días de libre disposición.',
                'color' => '#8b5cf6', // Purple
                'is_all_day' => true,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'team_id' => 1,
                'name' => 'Pausa',
                'observations' => 'Interrupción temporal de la jornada.',
                'color' => '#f59e0b', // Orange
                'is_all_day' => false,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => true,
                'is_authorizable' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'team_id' => 1,
                'name' => 'Evento Especial',
                'observations' => 'Eventos fuera de lo común.',
                'color' => '#ef4444', // Red
                'is_all_day' => false,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($eventTypes as $eventType) {
            \DB::table('event_types')->updateOrInsert(['id' => $eventType['id']], $eventType);
        }

        // Create Welcome Announcement
        if (\DB::table('team_announcements')->where('title', '¡Bienvenido a CTH!')->doesntExist()) {
            \DB::table('team_announcements')->insert([
                'team_id' => 1,
                'title' => '¡Bienvenido a CTH!',
                'content' => '<h2>¡Hola! Bienvenido a CTH (Control de Tiempo y Horarios)</h2>
                             <p>Esta aplicación ha sido diseñada para facilitar la gestión del tiempo laboral, permitiendo un registro preciso y transparente de la jornada de trabajo.</p>
                             <p><strong>¿Qué es CTH?</strong> Es una solución integral que combina una plataforma web avanzada con una aplicación móvil intuitiva, permitiendo fichajes mediante SmartClockIn, gestión de pausas, vacaciones y mucho más.</p>
                             <p><strong>¿Quién ha hecho esto?</strong> Este sistema ha sido desarrollado íntegramente por <strong>pbenav</strong>, con el objetivo de modernizar y simplificar el control horario empresarial.</p>
                             <p>Esperamos que esta herramienta te sea de gran utilidad. ¡Empecemos a trabajar!</p>',
                'format' => 'html',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Disable foreign key checks to avoid constraint errors during rollback
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('permission_audit_log');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('team_announcements');
        Schema::dropIfExists('exceptional_clock_in_tokens');
        Schema::dropIfExists('failed_login_attempts');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('message_user');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('user_meta');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('events_history');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_types');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');

        Schema::enableForeignKeyConstraints();
    }
};
