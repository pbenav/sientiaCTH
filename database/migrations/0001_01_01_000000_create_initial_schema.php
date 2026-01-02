<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Initial Database Schema for CTH v1.0
 * 
 * This migration consolidates all previous migrations into a single, optimized schema.
 * It includes all core tables, optimized indexes for performance, and proper foreign key constraints.
 * 
 * @version 1.0.0
 * @since 2026-01-02
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_code')->nullable()->index();
            $table->string('name');
            $table->string('family_name1')->nullable();
            $table->string('family_name2')->nullable();
            $table->string('email')->unique();
            $table->boolean('is_admin')->default(false)->index();
            $table->unsignedInteger('max_owned_teams')->default(5);
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

        // Password resets
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Failed jobs queue
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Personal access tokens (Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('last_used_at');
        });

        // Teams table
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
            $table->timestamps();
        });

        // Team-User pivot table
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable();
            $table->foreignId('custom_role_id')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });

        // Team invitations (Jetstream)
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('role')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'email']);
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });

        // Work Centers table
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

        // Event Types table
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

        // Events table - Main time tracking table
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

        // Events history table - Audit trail
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

        // Holidays table
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

        // User metadata table
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

        // Messages table - Internal messaging system
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

        // Message recipients pivot
        Schema::create('message_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Failed login attempts tracking
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->timestamp('timestamp');
            $table->integer('lockout_time')->nullable();
            $table->timestamps();

            $table->index('ip_address');
        });

        // Exceptional clock-in tokens
        Schema::create('exceptional_clock_in_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // Team announcements
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

        // Permissions system
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

        // Roles table
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

        // Permission-Role pivot
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('granted_at')->useCurrent();

            $table->unique(['permission_id', 'role_id']);
        });

        // User permissions (direct assignments)
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

        // Permission audit log
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

        // Add foreign key constraint to team_user after roles table exists
        Schema::table('team_user', function (Blueprint $table) {
            $table->foreign('custom_role_id')->references('id')->on('roles')->onDelete('set null');
        });

        // Create Welcome team
        \DB::table('teams')->insert([
            'id' => 1,
            'user_id' => 1, // Will be the admin user
            'name' => 'Bienvenida',
            'personal_team' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create global administrator user
        // Default password: "admin123" - MUST be changed on first login
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

        // Add admin to Welcome team
        \DB::table('team_user')->insert([
            'team_id' => 1,
            'user_id' => 1,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_log');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
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
        Schema::dropIfExists('teams');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};
