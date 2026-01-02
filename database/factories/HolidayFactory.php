<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Holiday Factory
 * 
 * Generates realistic Spanish holidays and team-specific days off.
 * 
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Holiday>
     */
    protected $model = Holiday::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::instance($this->faker->dateTimeThisYear());
        
        return [
            'team_id' => Team::factory(),
            'date' => $date->toDateString(),
            'name' => $this->faker->randomElement([
                'Día de la Constitución',
                'Día del Trabajador',
                'Fiesta Local',
                'Día de Andalucía',
                'Día de Reyes',
            ]),
            'type' => $this->faker->randomElement(['national', 'regional', 'local']),
            'observations' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    /**
     * Create a national holiday.
     *
     * @param string $name
     * @param string $date Format: 'Y-m-d'
     * @return static
     */
    public function national(string $name, string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'date' => $date,
            'type' => 'national',
        ]);
    }

    /**
     * Create a regional holiday (Andalucía).
     *
     * @param string $name
     * @param string $date Format: 'Y-m-d'
     * @return static
     */
    public function regional(string $name, string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'date' => $date,
            'type' => 'regional',
        ]);
    }

    /**
     * Create a local holiday (municipality).
     *
     * @param string $name
     * @param string $date Format: 'Y-m-d'
     * @return static
     */
    public function local(string $name, string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'date' => $date,
            'type' => 'local',
        ]);
    }
}
