<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Enums\Source;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = now()->addDays(fake()->numberBetween(5, 60))->startOfDay();

        return [
            'name' => fake()->name(),
            'phone' => '+8801'.fake()->numerify('#########'),
            'check_in' => $checkIn,
            'check_out' => $checkIn->copy()->addDays(fake()->numberBetween(1, 3)),
            'guests' => fake()->numberBetween(1, 4),
            'cottage_preference' => fake()->randomElement(['Riverview AC', 'Mangrove Eco', 'Family suite', null]),
            'message' => fake()->sentence(),
            'source' => fake()->randomElement([Source::Website, Source::Facebook, Source::WhatsApp]),
            'status' => InquiryStatus::New,
        ];
    }
}
