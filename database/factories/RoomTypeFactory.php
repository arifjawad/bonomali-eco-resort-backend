<?php

namespace Database\Factories;

use App\Enums\RoomCategory;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(3, true)).' Suite';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(RoomCategory::cases()),
            'vibe_tag' => fake()->word(),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'amenities' => ['Wi-Fi', 'Breakfast'],
            'base_price' => 5000,
            'max_guests' => 4,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
