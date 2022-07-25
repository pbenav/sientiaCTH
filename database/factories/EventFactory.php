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
        $date = $startime->format('d-m-Y');
        $time = $startime->format('H:m:s');
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
            'description' => $this->faker->sentence(),
            'is_open' => $isopen,
        ];
    }
}
