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
        $startime = $this->faker->dateTime();
        $isopen = $this->faker->boolean();
        if (! $isopen){
            $endtime = $startime;
            $endtime->modify('+8 hours');
        } else {
            $endtime = null;
        }
        return [
            'user_id' => 1,
            'user_code' => $this->faker->randomElement(['12345678', '87654321']),
            'start_time' => $startime,
            'end_time' => $endtime, 
            'description' => $this->faker->sentence(),
            'is_open' => $isopen,
        ];
    }
}
