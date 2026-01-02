<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\WorkCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Work Center Factory
 * 
 * Generates realistic work centers with NFC tags and geolocation.
 * 
 * @extends Factory<WorkCenter>
 */
class WorkCenterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<WorkCenter>
     */
    protected $model = WorkCenter::class;

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
                'Oficina Central',
                'Almacén Principal',
                'Sucursal Norte',
                'Centro Logístico',
                'Oficina Comercial',
            ]) . ' - ' . $this->faker->city(),
            'description' => $this->faker->optional(0.4)->sentence(),
            'location' => $this->faker->address(),
            'latitude' => $this->faker->latitude(36, 43), // Spain range
            'longitude' => $this->faker->longitude(-9, 4), // Spain range
            'nfc_tag_id' => null,
            'nfc_tag_name' => null,
            'nfc_requires_tag' => false,
        ];
    }

    /**
     * Create a work center with NFC tag requirement.
     *
     * @return static
     */
    public function withNFC(): static
    {
        $nfcId = strtoupper(Str::random(8));
        
        return $this->state(fn (array $attributes) => [
            'nfc_tag_id' => $nfcId,
            'nfc_tag_name' => 'NFC-' . $attributes['name'],
            'nfc_requires_tag' => true,
        ]);
    }

    /**
     * Create a work center without geolocation.
     *
     * @return static
     */
    public function withoutGeolocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Create a main office work center.
     *
     * @return static
     */
    public function mainOffice(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Oficina Central',
            'description' => 'Sede principal de la empresa',
        ]);
    }
}
