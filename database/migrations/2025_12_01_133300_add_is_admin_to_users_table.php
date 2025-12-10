<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists and column doesn't already exist
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('email');
            });
        }

        // Make the first user (ID 1) a global administrator if exists
        $firstUser = DB::table('users')->where('id', 1)->first();
        if ($firstUser) {
            DB::table('users')->where('id', 1)->update(['is_admin' => true]);
        } else {
            // If user 1 doesn't exist, make the oldest user admin
            $oldestUser = DB::table('users')->orderBy('id')->first();
            if ($oldestUser) {
                DB::table('users')->where('id', $oldestUser->id)->update(['is_admin' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }
};
