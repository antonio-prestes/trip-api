<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TripStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripRequest>
 */
class TripRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'destination' => $this->faker->city(),
            'departure_date' => $this->faker->dateTimeBetween('+0 days', '+1 month'),
            'return_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'status_id' => TripStatus::pending()->id
        ];
    }
}
