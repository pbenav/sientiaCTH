<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use App\Services\SmartClockInService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaxWorkdayDurationTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $team;

    protected $workdayType;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'UTC']);
        $this->service = app(SmartClockInService::class);
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->current_team_id = $this->team->id;
        $this->user->save();

        $this->workdayType = \App\Models\EventType::create([
            'name' => 'Workday',
            'team_id' => $this->team->id,
            'is_workday_type' => true
        ]);
    }

    /** @test */
    public function it_blocks_clock_out_if_max_duration_is_exceeded()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 60
        ]);

        // Create an open event that started 2 hours ago (120 min)
        $event = Event::create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'start' => Carbon::now('UTC')->subMinutes(120)->format('Y-m-d H:i:s'),
            'is_open' => true,
            'event_type_id' => $this->workdayType->id
        ]);

        $result = $this->service->clockOut($this->user, $event->id);

        $this->assertFalse($result['success']);
        $this->assertEquals(SmartClockInService::STATUS_MAX_DURATION_EXCEEDED, $result['status_code']);
        $this->assertEquals(60, $result['max_minutes']);
        $this->assertGreaterThanOrEqual(120, $result['current_minutes']);
    }

    /** @test */
    public function it_adjusts_start_time_on_clock_out()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 60
        ]);

        $event = Event::create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'start' => Carbon::now('UTC')->subMinutes(120)->format('Y-m-d H:i:s'),
            'is_open' => true,
            'event_type_id' => $this->workdayType->id
        ]);

        $result = $this->service->clockOutWithAdjustment($this->user, $event->id, 'adjust_start');

        $this->assertTrue($result['success']);
        $event->refresh();
        $this->assertFalse($event->is_open);
        
        $start = Carbon::parse($event->start);
        $end = Carbon::parse($event->end);
        $this->assertEquals(60, $end->diffInMinutes($start));
        $this->assertStringContainsString('Ajuste de hora de inicio', $event->observations);
    }

    /** @test */
    public function it_adjusts_end_time_on_clock_out()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 60
        ]);

        $startTime = Carbon::now('UTC')->subMinutes(120);
        $event = Event::create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'start' => $startTime->format('Y-m-d H:i:s'),
            'is_open' => true,
            'event_type_id' => $this->workdayType->id
        ]);

        $result = $this->service->clockOutWithAdjustment($this->user, $event->id, 'adjust_end');

        $this->assertTrue($result['success']);
        $event->refresh();
        
        $start = Carbon::parse($event->start);
        $end = Carbon::parse($event->end);
        $this->assertEquals(60, $end->diffInMinutes($start));
        $this->assertEquals($startTime->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('Ajuste de hora de salida', $event->observations);
    }

    /** @test */
    public function it_correctly_calculates_duration_with_non_utc_timezone()
    {
        // Set team timezone to Europe/Madrid (+1/+2)
        $this->team->update([
            'timezone' => 'Europe/Madrid',
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 60
        ]);

        // If it's 14:00 Madrid, and event started at 12:30 Madrid.
        // Duration is 90 mins.
        // In UTC: Now is 13:00 (if Winter) or 12:00 (if Summer).
        // Let's use fixed times to be sure.
        
        $startTimeUTC = Carbon::parse('2026-01-20 10:00:00', 'UTC');
        $nowUTC = Carbon::parse('2026-01-20 11:30:00', 'UTC'); // 90 min difference
        
        Carbon::setTestNow($nowUTC);

        $event = Event::create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'start' => $startTimeUTC->toDateTimeString(), // Use string format
            'is_open' => true,
            'event_type_id' => $this->workdayType->id
        ]);

        // Force the event start to be exact UTC for the test case
        $event->update(['start' => $startTimeUTC]);

        $result = $this->service->clockOut($this->user, $event->id);

        $this->assertFalse($result['success']);
        $this->assertEquals(90, $result['current_minutes']);
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function it_validates_max_duration_in_get_time_registers()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 60
        ]);

        // Create an OPEN event that started 120 minutes ago
        // Use withoutEvents to bypass the Observer during test setup
        $event = Event::withoutEvents(function () {
            $event = Event::create([
                'user_id' => $this->user->id,
                'team_id' => $this->team->id,
                'start' => Carbon::now('UTC')->subMinutes(120)->toDateTimeString(),
                'end' => null, // Open event
                'is_open' => true,
                'event_type_id' => $this->workdayType->id
            ]);
            
            // Now manually set the end time to now (which would make it 120 min duration)
            $event->end = Carbon::now('UTC')->toDateTimeString();
            $event->save();
            
            return $event;
        });
        
        // Now try to CLOSE it via GetTimeRegisters::confirm
        // The manual validation in GetTimeRegisters should catch this
        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Http\Livewire\GetTimeRegisters::class)
            ->call('confirm', $event->id)
            ->assertEmitted('alertFail');
            
        $event->refresh();
        $this->assertTrue($event->is_open); // Should still be open
    }

    /** @test */
    public function it_validates_total_daily_duration_with_multiple_shifts()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 480 // 8 hours
        ]);

        // Create first shift: 08:00 - 12:00 (4 hours) - CLOSED
        Event::withoutEvents(function () {
            return Event::create([
                'user_id' => $this->user->id,
                'team_id' => $this->team->id,
                'start' => Carbon::parse('2026-01-21 08:00:00', 'UTC')->toDateTimeString(),
                'end' => Carbon::parse('2026-01-21 12:00:00', 'UTC')->toDateTimeString(),
                'is_open' => false,
                'event_type_id' => $this->workdayType->id
            ]);
        });

        // Create second shift: started 5 hours ago - OPEN
        $secondShift = Event::create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'start' => Carbon::now('UTC')->subHours(5)->toDateTimeString(),
            'is_open' => true,
            'event_type_id' => $this->workdayType->id
        ]);

        // Try to clock out (total would be 4h + 5h = 9h > 8h limit)
        $result = $this->service->clockOut($this->user, $secondShift->id);

        $this->assertFalse($result['success']);
        $this->assertEquals(SmartClockInService::STATUS_MAX_DURATION_EXCEEDED, $result['status_code']);
        $this->assertEquals(480, $result['max_minutes']);
        $this->assertGreaterThanOrEqual(540, $result['current_minutes']); // 9 hours = 540 min
    }

    /** @test */
    public function it_blocks_event_creation_via_observer_if_total_daily_duration_exceeded()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 480 // 8 hours
        ]);

        // 1. Create first shift: 09:00 - 14:00 (5 hours)
        Event::create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'start' => Carbon::parse('2026-01-21 09:00:00', 'UTC'),
            'end' => Carbon::parse('2026-01-21 14:00:00', 'UTC'),
            'is_open' => false,
            'event_type_id' => $this->workdayType->id
        ]);

        // 2. Try to create second shift: 16:00 - 22:00 (6 hours)
        // Total: 11 hours -> Should fail validation via Observer
        try {
            Event::create([
                'user_id' => $this->user->id,
                'team_id' => $this->team->id,
                'start' => Carbon::parse('2026-01-21 16:00:00', 'UTC'),
                'end' => Carbon::parse('2026-01-21 22:00:00', 'UTC'),
                'is_open' => false,
                'event_type_id' => $this->workdayType->id
            ]);
            
            $this->fail('Event should not have been created due to max duration exceeded');
        } catch (\App\Exceptions\MaxWorkdayDurationExceededException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_distributes_across_all_schedule_slots_ignoring_day_of_week()
    {
        $this->team->update([
            'force_max_workday_duration' => true,
            'max_workday_duration_minutes' => 480 // 8 hours
        ]);

        // Set up schedule with different days for different slots
        // Slot 1: 09:00-14:00 (days: [1,3,5]) - Mon, Wed, Fri only
        // Slot 2: 16:00-20:00 (days: [1,2,3,4,5]) - Mon-Fri
        $schedule = [
            ['start' => '09:00', 'end' => '14:00', 'days' => [1, 3, 5]],
            ['start' => '16:00', 'end' => '20:00', 'days' => [1, 2, 3, 4, 5]],
        ];
        
        $this->user->meta()->updateOrCreate(
            ['meta_key' => 'work_schedule'],
            ['meta_value' => json_encode($schedule)]
        );

        // Create event on TUESDAY (day 2) - Slot 1 doesn't include Tuesday
        // Event: 09:00-23:00 (14 hours) needs adjustment to 8 hours
        // Using withoutEvents to create the initial event without triggering validation
        $event = Event::withoutEvents(function () {
            return Event::create([
                'user_id' => $this->user->id,
                'team_id' => $this->team->id,
                'start' => Carbon::parse('2026-02-18 08:00:00', 'UTC'), // Tuesday 08:00 UTC = 09:00 Madrid
                'end' => Carbon::parse('2026-02-18 22:00:00', 'UTC'),   // Tuesday 22:00 UTC = 23:00 Madrid
                'is_open' => false,
                'event_type_id' => $this->workdayType->id
            ]);
        });

        // Now try to adjust it using the service (simulates the adjust_schedule action)
        $service = app(SmartClockInService::class);
        $result = $service->clockOutWithAdjustment($this->user, $event->id, 'adjust_schedule');

        // Verify the adjustment was successful
        $this->assertTrue($result['success'], 'Adjustment should succeed');

        // Get all events for this day
        $allEvents = Event::where('user_id', $this->user->id)
            ->where('team_id', $this->team->id)
            ->whereDate('start', '2026-02-18')
            ->orderBy('start')
            ->get();

        $this->assertGreaterThanOrEqual(2, $allEvents->count(), 'Should create at least 2 events across both slots');
        
        // Verify total doesn't exceed limit
        $totalMinutes = 0;
        foreach ($allEvents as $evt) {
            $start = Carbon::parse($evt->start);
            $end = Carbon::parse($evt->end);
            $minutes = $end->diffInMinutes($start);
            $totalMinutes += $minutes;
            
            // Log for debugging
            \Log::info("Event slot: {$start->format('H:i')} - {$end->format('H:i')} ({$minutes} min)");
        }
        
        $this->assertLessThanOrEqual(480, $totalMinutes, 'Total should not exceed 480 minutes');
        $this->assertGreaterThan(0, $totalMinutes, 'Total should be greater than 0');
    }
}
