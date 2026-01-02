<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkCenter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Event Factory
 * 
 * Generates realistic time tracking events with proper date ranges,
 * overlaps, and edge cases for testing dashboards and statistics.
 * 
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Event>
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::instance($this->faker->dateTimeThisYear());
        $isOpen = $this->faker->boolean(10); // 10% chance of being open
        
        $end = $isOpen ? null : (clone $start)->addHours($this->faker->numberBetween(4, 10));
        
        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'start' => $start,
            'end' => $end,
            'description' => $this->faker->optional(0.3)->sentence(3),
            'event_type_id' => null, // Will be set by seeders to existing types
            'observations' => $this->faker->optional(0.2)->sentence(),
            'work_center_id' => null,
            'authorized_by_id' => null,
            'is_open' => $isOpen,
            'is_authorized' => false,
            'is_closed_automatically' => $this->faker->boolean(20),
            'is_exceptional' => false,
            'is_extra_hours' => $this->faker->boolean(15),
            'latitude' => null,
            'longitude' => null,
            'ip_address' => $this->faker->optional(0.4)->ipv4(),
            'nfc_tag_id' => null,
        ];
    }

    /**
     * Create an open event (not yet closed).
     *
     * @return static
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'end' => null,
            'is_open' => true,
        ]);
    }

    /**
     * Create a closed event with proper duration.
     *
     * @param int $hours Duration in hours
     * @return static
     */
    public function closed(int $hours = 8): static
    {
        return $this->state(function (array $attributes) use ($hours) {
            $start = Carbon::parse($attributes['start']);
            return [
                'end' => (clone $start)->addHours($hours),
                'is_open' => false,
            ];
        });
    }

    /**
     * Create an event from the past (last 12 months).
     *
     * @return static
     */
    public function past(): static
    {
        $start = Carbon::now()->subMonths($this->faker->numberBetween(1, 12));
        return $this->state(fn (array $attributes) => [
            'start' => $start,
            'end' => (clone $start)->addHours($this->faker->numberBetween(4, 10)),
            'is_open' => false,
        ]);
    }

    /**
     * Create an event in the future (next 3 months).
     *
     * @return static
     */
    public function future(): static
    {
        $start = Carbon::now()->addDays($this->faker->numberBetween(1, 90));
        return $this->state(fn (array $attributes) => [
            'start' => $start,
            'end' => (clone $start)->addHours($this->faker->numberBetween(4, 10)),
            'is_open' => false,
        ]);
    }

    /**
     * Create an exceptional event.
     *
     * @return static
     */
    public function exceptional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_exceptional' => true,
            'observations' => 'Exceptional event - ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create an event with geolocation data.
     *
     * @return static
     */
    public function withGeolocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(36, 43), // Spain range
            'longitude' => $this->faker->longitude(-9, 4), // Spain range
        ]);
    }

    /**
     * Create an event requiring authorization.
     *
     * @return static
     */
    public function requiresAuthorization(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_authorized' => false,
            'observations' => 'Pending authorization',
        ]);
    }

    /**
     * Create an authorized event.
     *
     * @return static
     */
    public function authorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_authorized' => true,
            'authorized_by_id' => User::factory(),
        ]);
    }
}
