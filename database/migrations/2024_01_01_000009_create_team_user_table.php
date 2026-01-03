<?php

declare(strict_types=1);

use App\Support\Permissions\PermissionMatrix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Team User Pivot Table Migration
 * 
 * Links users to teams with roles.
 * Depends on: users, teams, roles
 */
return new class extends Migration
{
    public function up(): void
    {
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
            $this->addMissingColumns();
        }

        // Link admin to Welcome team
        $this->linkAdminToWelcomeTeam();

        // Initialize permissions and roles
        $this->initializePermissionsAndRoles();
    }

    private function addMissingColumns(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            if (!Schema::hasColumn('team_user', 'custom_role_id')) {
                $table->foreignId('custom_role_id')->nullable()->after('role')->constrained('roles')->onDelete('set null');
            }
        });
    }

    private function linkAdminToWelcomeTeam(): void
    {
        if (DB::table('team_user')->where('team_id', 1)->where('user_id', 1)->doesntExist()) {
            DB::table('team_user')->insert([
                'team_id' => 1,
                'user_id' => 1,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update user's current_team_id if not set
        DB::table('users')
            ->where('id', 1)
            ->whereNull('current_team_id')
            ->update(['current_team_id' => 1]);
    }

    private function initializePermissionsAndRoles(): void
    {
        // Seed base permissions and system roles for the Welcome team
        PermissionMatrix::syncTeamRoles(1, 1);
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
