<?php

namespace Database\Factories;

use App\Enums\Source;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+8801'.fake()->numerify('#########'),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->optional()->city(),
            'source' => fake()->randomElement(Source::cases()),
            'tags' => [],
        ];
    }
}
