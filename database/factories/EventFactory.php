<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'startTime' => $this->faker->dateTime(),
            'endTime' => $this->faker->dateTime(),
            'userId' => $this->faker->randomDigitNotNull(),
            'description' => $this->faker->sentence(),
        ];
    }
}
