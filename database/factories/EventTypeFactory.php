<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventType;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Event Type Factory
 * 
 * Generates common event types for testing (Workday, Vacation, Sick Leave, etc.).
 * 
 * @extends Factory<EventType>
 */
class EventTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EventType>
     */
    protected $model = EventType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->randomElement([
                'Jornada Laboral',
                'Vacaciones',
                'Baja Médica',
                'Formación',
                'Teletrabajo',
                'Reunión',
            ]),
            'observations' => $this->faker->optional(0.3)->sentence(),
            'color' => $this->faker->hexColor(),
            'is_all_day' => false,
            'is_workday_type' => true,
            'is_break_type' => false,
            'is_authorizable' => false,
            'is_pause_type' => false,
        ];
    }

    /**
     * Create a workday event type.
     *
     * @return static
     */
    public function workday(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Jornada Laboral',
            'color' => '#10b981',
            'is_workday_type' => true,
        ]);
    }

    /**
     * Create a break/pause event type.
     *
     * @return static
     */
    public function breakTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pausa',
            'color' => '#f59e0b',
            'is_break_type' => true,
            'is_pause_type' => true,
            'is_workday_type' => false,
        ]);
    }

    /**
     * Create a vacation event type.
     *
     * @return static
     */
    public function vacation(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Vacaciones',
            'color' => '#3b82f6',
            'is_all_day' => true,
            'is_workday_type' => false,
            'is_authorizable' => true,
        ]);
    }

    /**
     * Create a sick leave event type.
     *
     * @return static
     */
    public function sickLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Baja Médica',
            'color' => '#ef4444',
            'is_all_day' => true,
            'is_workday_type' => false,
        ]);
    }

    /**
     * Create an event type that requires authorization.
     *
     * @return static
     */
    public function requiresAuthorization(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_authorizable' => true,
        ]);
    }
}
