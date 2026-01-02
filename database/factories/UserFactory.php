<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * User Factory
 * 
 * Generates realistic test users with Spanish names and proper validation.
 * 
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $familyName1 = $this->faker->lastName();
        $familyName2 = $this->faker->lastName();
        
        return [
            'user_code' => (string) $this->faker->unique()->numberBetween(10000000, 99999999),
            'name' => $firstName,
            'family_name1' => $familyName1,
            'family_name2' => $familyName2,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password for testing
            'remember_token' => Str::random(10),
            'is_admin' => false,
            'max_owned_teams' => 5,
            'week_starts_on' => 1, // Monday
            'vacation_calculation_type' => $this->faker->randomElement(['natural', 'working']),
            'vacation_working_days' => 22,
            'geolocation_enabled' => $this->faker->boolean(30), // 30% chance
            'notify_new_messages' => true,
        ];
    }

    /**
     * Indicate that the user is an administrator.
     *
     * @return static
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'max_owned_teams' => 999,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has two-factor authentication enabled.
     *
     * @return static
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that geolocation is enabled for this user.
     *
     * @return static
     */
    public function withGeolocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'geolocation_enabled' => true,
        ]);
    }
}
