<?php

namespace Database\Factories;

use DateTime;
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
        $startime = $this->faker->dateTimeThisYear();
        $isopen = $this->faker->boolean();
        if (!$isopen) {
            $endtime = $startime;
            $endtime->modify('+8 hours');
        } else {
            $endtime = null;
        }
        return [
            'user_id' => 1,
            'start' => $startime,
            'end' => $endtime,
            'description' => 'Workday',
            'is_open' => $isopen,
        ];
    }
}
