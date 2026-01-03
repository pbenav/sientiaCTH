<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerifyDatabaseSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:verify-schema 
                            {--fix : Automatically fix missing columns}
                            {--table= : Check only a specific table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify database schema and optionally fix missing columns';

    /**
     * Expected schema definition
     */
    protected array $expectedColumns = [
        'users' => [
            'locale' => [
                'type' => 'string',
                'length' => 5,
                'default' => 'es',
                'nullable' => false,
                'after' => 'notify_new_messages',
                'comment' => 'User preferred language (es, en)'
            ],
        ],
        'teams' => [
            'max_member_teams' => [
                'type' => 'unsignedInteger',
                'default' => 5,
                'nullable' => false,
                'after' => 'special_event_color',
                'comment' => 'Maximum number of teams that members of this team can create'
            ],
        ],
        'team_user' => [
            'custom_role_id' => [
                'type' => 'foreignId',
                'nullable' => true,
                'after' => 'role',
                'references' => 'id',
                'on' => 'roles',
                'onDelete' => 'set null'
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Verifying database schema...');
        $this->newLine();

        $missingColumns = [];
        $tablesToCheck = $this->option('table') 
            ? [$this->option('table')] 
            : array_keys($this->expectedColumns);

        foreach ($tablesToCheck as $table) {
            if (!isset($this->expectedColumns[$table])) {
                $this->warn("⚠️  Table '{$table}' not in verification list");
                continue;
            }

            if (!Schema::hasTable($table)) {
                $this->error("❌ Table '{$table}' does not exist!");
                continue;
            }

            $this->info("Checking table: {$table}");
            
            foreach ($this->expectedColumns[$table] as $column => $definition) {
                if (!Schema::hasColumn($table, $column)) {
                    $missingColumns[$table][$column] = $definition;
                    $this->warn("  ⚠️  Missing column: {$column}");
                } else {
                    $this->line("  ✓ Column exists: {$column}");
                }
            }
        }

        if (empty($missingColumns)) {
            $this->newLine();
            $this->info('✅ All expected columns are present!');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->warn('⚠️  Found missing columns:');
        foreach ($missingColumns as $table => $columns) {
            $this->warn("  {$table}: " . implode(', ', array_keys($columns)));
        }

        if ($this->option('fix')) {
            $this->newLine();
            if ($this->confirm('Do you want to add the missing columns?', true)) {
                $this->addMissingColumns($missingColumns);
                return Command::SUCCESS;
            }
        } else {
            $this->newLine();
            $this->info('💡 Run with --fix option to automatically add missing columns:');
            $this->line('   php artisan db:verify-schema --fix');
        }

        return Command::FAILURE;
    }

    /**
     * Add missing columns to database
     */
    protected function addMissingColumns(array $missingColumns): void
    {
        $this->newLine();
        $this->info('🔧 Adding missing columns...');

        foreach ($missingColumns as $table => $columns) {
            $this->info("Processing table: {$table}");

            Schema::table($table, function ($tableBlueprint) use ($columns) {
                foreach ($columns as $column => $definition) {
                    $this->addColumn($tableBlueprint, $column, $definition);
                }
            });

            $this->info("  ✅ Added " . count($columns) . " column(s) to {$table}");
        }

        $this->newLine();
        $this->info('✅ Schema update completed successfully!');
    }

    /**
     * Add a column based on definition
     */
    protected function addColumn($table, string $column, array $definition): void
    {
        $col = null;

        switch ($definition['type']) {
            case 'string':
                $col = $table->string($column, $definition['length'] ?? 255);
                break;
            case 'unsignedInteger':
                $col = $table->unsignedInteger($column);
                break;
            case 'foreignId':
                $col = $table->foreignId($column);
                break;
            default:
                $this->error("  ❌ Unknown column type: {$definition['type']}");
                return;
        }

        // Apply modifiers
        if (isset($definition['nullable']) && $definition['nullable']) {
            $col->nullable();
        }

        if (isset($definition['default'])) {
            $col->default($definition['default']);
        }

        if (isset($definition['after'])) {
            $col->after($definition['after']);
        }

        if (isset($definition['comment'])) {
            $col->comment($definition['comment']);
        }

        // Foreign key constraint
        if ($definition['type'] === 'foreignId' && isset($definition['on'])) {
            $col->constrained($definition['on']);
            
            if (isset($definition['onDelete'])) {
                $col->onDelete($definition['onDelete']);
            }
        }

        $this->line("  ✓ Added column: {$column}");
    }
}
