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
            'userId' => 1,
            'userCode' => $this->faker->randomElement(['12345678', '87654321']),
            'startTime' => $startime,
            'endTime' => $endtime, 
            'description' => $this->faker->sentence(),
            'isOpen' => $isopen,
        ];
    }
}
