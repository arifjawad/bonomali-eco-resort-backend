<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\Source;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Raw bookings for tests. Real bookings go through BookingService,
 * which checks availability and computes the total.
 *
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = now()->addDays(fake()->numberBetween(1, 30))->startOfDay();

        return [
            'reference' => 'BNM-TEST-'.fake()->unique()->numerify('#####'),
            'guest_id' => Guest::factory(),
            'room_id' => Room::factory(),
            'check_in' => $checkIn,
            'check_out' => $checkIn->copy()->addDays(2),
            'adults' => 2,
            'children' => 0,
            'status' => BookingStatus::Confirmed,
            'source' => Source::WhatsApp,
            'total_amount' => 10000,
        ];
    }

    public function status(BookingStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
