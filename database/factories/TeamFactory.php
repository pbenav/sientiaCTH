<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Team Factory
 * 
 * Generates realistic test teams with proper configuration.
 * 
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Team>
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->company(),
            'personal_team' => false,
            'pdf_engine' => 'browsershot',
            'max_report_months' => 3,
            'async_report_threshold_months' => 6,
            'event_retention_months' => 60,
            'timezone' => 'Europe/Madrid',
            'event_expiration_days' => null,
            'force_clock_in_delay' => false,
            'clock_in_delay_minutes' => null,
            'clock_in_grace_period_minutes' => null,
            'special_event_color' => $this->faker->hexColor(),
        ];
    }

    /**
     * Indicate that this is a personal team.
     *
     * @return static
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'personal_team' => true,
            'name' => 'Personal Team',
        ]);
    }

    /**
     * Indicate that this team requires clock-in delay.
     *
     * @return static
     */
    public function withClockInDelay(): static
    {
        return $this->state(fn (array $attributes) => [
            'force_clock_in_delay' => true,
            'clock_in_delay_minutes' => $this->faker->numberBetween(5, 30),
            'clock_in_grace_period_minutes' => $this->faker->numberBetween(1, 5),
        ]);
    }

    /**
     * Configure team with specific timezone.
     *
     * @param string $timezone
     * @return static
     */
    public function inTimezone(string $timezone): static
    {
        return $this->state(fn (array $attributes) => [
            'timezone' => $timezone,
        ]);
    }
}
