<?php

namespace Database\Factories;

use App\Models\Rate;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rate>
 */
class RateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'name' => 'Peak season',
            'starts_on' => now()->addMonth()->startOfMonth(),
            'ends_on' => now()->addMonth()->endOfMonth(),
            'price_per_night' => 8000,
        ];
    }
}
