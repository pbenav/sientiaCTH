<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Work Centers Table Migration
 * 
 * Physical locations for clock-ins with NFC support.
 * Depends on: teams
 */
return new class extends Migration
{
    public function up(): void
    {
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

                $table->index('nfc_tag_id');
                $table->index('nfc_payload');
            });
        } else {
            $this->addMissingColumns();
        }

        // Ensure default work center exists
        $this->ensureDefaultWorkCenter();
    }

    private function addMissingColumns(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            if (!Schema::hasColumn('work_centers', 'nfc_tag_id')) {
                $table->string('nfc_tag_id', 64)->nullable()->unique()->after('code');
            }
            if (!Schema::hasColumn('work_centers', 'nfc_tag_description')) {
                $table->text('nfc_tag_description')->nullable()->after('nfc_tag_id');
            }
            if (!Schema::hasColumn('work_centers', 'nfc_payload')) {
                $table->string('nfc_payload', 500)->nullable()->after('nfc_tag_description');
            }
            if (!Schema::hasColumn('work_centers', 'nfc_tag_generated_at')) {
                $table->timestamp('nfc_tag_generated_at')->nullable()->after('nfc_payload');
            }
        });
    }

    private function ensureDefaultWorkCenter(): void
    {
        if (DB::table('work_centers')->where('id', 1)->doesntExist()) {
            DB::table('work_centers')->insert([
                'id' => 1,
                'team_id' => 1,
                'name' => 'Sede Central',
                'code' => 'HQ-001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('work_centers');
    }
};
