<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Holiday;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkCenter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Database Seeder
 * 
 * Intelligent seeder that preserves existing configuration data while
 * generating comprehensive test data for development and testing.
 * 
 * PRESERVATION STRATEGY:
 * - Preserves admin user (id=1) if exists
 * - Preserves existing EventTypes (20 production types)
 * - Preserves existing WorkCenters (7 production locations)
 * - Preserves existing Holidays
 * - Preserves existing Permissions (91)
 * - Preserves existing Roles (16)
 * 
 * GENERATION STRATEGY:
 * - Creates 15 test users with realistic Spanish names
 * - Creates 3 test teams with different configurations
 * - Generates events for past 12 months + future 3 months
 * - Creates realistic time tracking patterns (8-hour shifts)
 * - Includes edge cases: overlapping events, open events, exceptional events
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting intelligent database seeding...');

        // Step 1: Ensure admin user exists
        $admin = $this->ensureAdminExists();
        $this->command->info("✅ Admin user verified (ID: {$admin->id})");

        // Step 2: Preserve or seed configuration data
        $eventTypes = EventType::count();
        $workCenters = WorkCenter::count();
        $this->command->info("✅ Configuration preserved: {$eventTypes} event types, {$workCenters} work centers");

        // Step 3: Create test users
        $testUsers = $this->createTestUsers($admin);
        $this->command->info("✅ Created " . count($testUsers) . " test users");

        // Step 4: Create test teams
        $teams = $this->createTestTeams($admin, $testUsers);
        $this->command->info("✅ Created " . count($teams) . " test teams");

        // Step 5: Assign users to teams
        $this->assignUsersToTeams($teams, $testUsers);
        $this->command->info("✅ Users assigned to teams");

        // Step 6: Generate realistic events (past + future)
        $eventsCreated = $this->generateEvents($teams, $testUsers, $eventTypes);
        $this->command->info("✅ Generated {$eventsCreated} events (12 months past + 3 months future)");

        // Step 7: Call additional seeders if needed
        if (WorkCenter::count() === 0) {
            $this->call(WorkCenterSeeder::class);
            $this->command->info("✅ Work centers seeded");
        }

        $this->command->info('🎉 Database seeding completed successfully!');
    }

    /**
     * Ensure admin user exists (ID: 1).
     *
     * @return User
     */
    private function ensureAdminExists(): User
    {
        $admin = User::find(1);

        if (!$admin) {
            $admin = User::create([
                'id' => 1,
                'name' => 'Administrador',
                'email' => 'informatica@zafarraya.es',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'current_team_id' => null,
            ]);

            $this->command->warn('⚠️  Admin user created (default password: "password")');
        }

        return $admin;
    }

    /**
     * Create test users with realistic Spanish names.
     *
     * @param User $admin
     * @return array<User>
     */
    private function createTestUsers(User $admin): array
    {
        $users = [];

        // Create 15 test users with varied configurations
        for ($i = 0; $i < 15; $i++) {
            $user = User::factory()->create();
            
            // 20% of users have 2FA enabled
            if ($i % 5 === 0) {
                $user->two_factor_secret = encrypt('test-secret');
                $user->save();
            }

            // 30% of users have geolocation tracking
            if ($i % 3 === 0) {
                $user->geolocation_tracking = true;
                $user->save();
            }

            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create test teams with different configurations.
     *
     * @param User $admin
     * @param array<User> $testUsers
     * @return array<Team>
     */
    private function createTestTeams(User $admin, array $testUsers): array
    {
        $teams = [];

        // Team 1: Standard team without restrictions
        $teams[] = Team::factory()->create([
            'user_id' => $admin->id,
            'name' => 'Equipo Desarrollo',
            'timezone' => 'Europe/Madrid',
        ]);

        // Team 2: Team with clock-in delay requirement
        $teams[] = Team::factory()->withClockInDelay()->create([
            'user_id' => $admin->id,
            'name' => 'Equipo Producción',
        ]);

        // Team 3: Team with strict event expiration
        $teams[] = Team::factory()->create([
            'user_id' => $admin->id,
            'name' => 'Equipo Comercial',
            'event_expiration_days' => 7,
        ]);

        return $teams;
    }

    /**
     * Assign test users to teams via team_user pivot.
     *
     * @param array<Team> $teams
     * @param array<User> $testUsers
     * @return void
     */
    private function assignUsersToTeams(array $teams, array $testUsers): void
    {
        foreach ($testUsers as $index => $user) {
            // Distribute users across teams
            $team = $teams[$index % count($teams)];
            
            // Attach user to team
            DB::table('team_user')->insert([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'role' => ($index % 5 === 0) ? 'admin' : 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Set current team for user
            $user->current_team_id = $team->id;
            $user->save();
        }
    }

    /**
     * Generate realistic events for past 12 months + future 3 months.
     *
     * @param array<Team> $teams
     * @param array<User> $testUsers
     * @param int $eventTypeCount
     * @return int Number of events created
     */
    private function generateEvents(array $teams, array $testUsers, int $eventTypeCount): int
    {
        $eventsCreated = 0;
        $eventTypeIds = EventType::pluck('id')->toArray();

        if (empty($eventTypeIds)) {
            $this->command->warn('⚠️  No event types found, skipping event generation');
            return 0;
        }

        // Get first event type for workday events
        $workdayTypeId = $eventTypeIds[0] ?? null;

        // Generate events for each user
        foreach ($testUsers as $user) {
            $team = Team::find($user->current_team_id);
            
            if (!$team) {
                continue;
            }

            // Past events (last 12 months): ~240 workdays
            $eventsCreated += $this->generatePastEvents($user, $team, $workdayTypeId);

            // Future events (next 3 months): ~60 workdays
            $eventsCreated += $this->generateFutureEvents($user, $team, $workdayTypeId);

            // Add some edge cases for testing
            $eventsCreated += $this->generateEdgeCaseEvents($user, $team, $eventTypeIds);
        }

        return $eventsCreated;
    }

    /**
     * Generate past events (last 12 months).
     *
     * @param User $user
     * @param Team $team
     * @param int|null $eventTypeId
     * @return int
     */
    private function generatePastEvents(User $user, Team $team, ?int $eventTypeId): int
    {
        $count = 0;
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();

        // Generate weekday events (Mon-Fri)
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                // 90% chance of event on weekdays
                if (rand(1, 100) <= 90) {
                    $start = $current->copy()->setTime(8, rand(0, 30), 0);
                    $end = $start->copy()->addHours(8)->addMinutes(rand(0, 30));

                    Event::create([
                        'user_id' => $user->id,
                        'team_id' => $team->id,
                        'start' => $start,
                        'end' => $end,
                        'event_type_id' => $eventTypeId,
                        'is_open' => false,
                        'is_authorized' => true,
                    ]);

                    $count++;
                }
            }

            $current->addDay();
        }

        return $count;
    }

    /**
     * Generate future events (next 3 months).
     *
     * @param User $user
     * @param Team $team
     * @param int|null $eventTypeId
     * @return int
     */
    private function generateFutureEvents(User $user, Team $team, ?int $eventTypeId): int
    {
        $count = 0;
        $startDate = Carbon::now()->addDay();
        $endDate = Carbon::now()->addMonths(3);

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $start = $current->copy()->setTime(8, 0, 0);
                $end = $start->copy()->addHours(8);

                Event::create([
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'start' => $start,
                    'end' => $end,
                    'event_type_id' => $eventTypeId,
                    'is_open' => false,
                    'is_authorized' => false,
                ]);

                $count++;
            }

            $current->addDay();
        }

        return $count;
    }

    /**
     * Generate edge case events for testing.
     *
     * @param User $user
     * @param Team $team
     * @param array<int> $eventTypeIds
     * @return int
     */
    private function generateEdgeCaseEvents(User $user, Team $team, array $eventTypeIds): int
    {
        $count = 0;

        // 1. Open event (not yet closed)
        Event::create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'start' => Carbon::now()->subHours(2),
            'end' => null,
            'event_type_id' => $eventTypeIds[0] ?? null,
            'is_open' => true,
        ]);
        $count++;

        // 2. Overlapping event (for validation testing)
        $start = Carbon::now()->subDays(5)->setTime(10, 0, 0);
        Event::create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'start' => $start,
            'end' => $start->copy()->addHours(4),
            'event_type_id' => $eventTypeIds[0] ?? null,
            'is_open' => false,
        ]);
        $count++;

        // 3. Exceptional event
        Event::create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'start' => Carbon::now()->subDays(10)->setTime(8, 0, 0),
            'end' => Carbon::now()->subDays(10)->setTime(16, 0, 0),
            'event_type_id' => $eventTypeIds[0] ?? null,
            'is_exceptional' => true,
            'observations' => 'Exceptional work event for testing',
            'is_open' => false,
        ]);
        $count++;

        // 4. Event with geolocation (if user has it enabled)
        if ($user->geolocation_tracking) {
            Event::create([
                'user_id' => $user->id,
                'team_id' => $team->id,
                'start' => Carbon::now()->subDays(3)->setTime(8, 0, 0),
                'end' => Carbon::now()->subDays(3)->setTime(16, 0, 0),
                'event_type_id' => $eventTypeIds[0] ?? null,
                'latitude' => 37.0522,
                'longitude' => -3.9214,
                'is_open' => false,
            ]);
            $count++;
        }

        return $count;
    }
}
