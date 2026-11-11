<?php

namespace Tests\Feature\Services;

use App\Enums\BookingStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Rate;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookings;

    private Room $room;

    private Guest $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-11-12 10:00');
        $this->bookings = app(BookingService::class);
        $this->room = Room::factory()->for(RoomType::factory()->state(['base_price' => 5500, 'max_guests' => 3]))->create();
        $this->guest = Guest::factory()->create();
    }

    public function test_creates_a_pending_booking_with_reference_and_total(): void
    {
        $user = User::factory()->create();

        $booking = $this->bookings->create($this->data(), $user);

        $this->assertSame('BNM-2026-0001', $booking->reference);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(2 * 5500, $booking->total_amount);
        $this->assertSame($user->id, $booking->created_by);
    }

    public function test_references_count_up_within_the_year(): void
    {
        $this->bookings->create($this->data(['check_in' => '2026-12-01', 'check_out' => '2026-12-02']));
        $second = $this->bookings->create($this->data(['check_in' => '2026-12-05', 'check_out' => '2026-12-06']));

        $this->assertSame('BNM-2026-0002', $second->reference);
    }

    public function test_total_uses_seasonal_rate(): void
    {
        Rate::factory()->for($this->room->roomType)->create([
            'starts_on' => '2026-12-11', 'ends_on' => '2026-12-31', 'price_per_night' => 7000,
        ]);

        $booking = $this->bookings->create($this->data());

        $this->assertSame(5500 + 7000, $booking->total_amount);
    }

    public function test_rejects_double_booking(): void
    {
        $this->bookings->create($this->data());

        $this->expectException(BookingException::class);

        $this->bookings->create($this->data(['check_in' => '2026-12-11', 'check_out' => '2026-12-13']));
    }

    public function test_allows_back_to_back_stays(): void
    {
        $this->bookings->create($this->data());

        $next = $this->bookings->create($this->data(['check_in' => '2026-12-12', 'check_out' => '2026-12-13']));

        $this->assertNotNull($next->id);
    }

    public function test_rejects_check_out_on_or_before_check_in(): void
    {
        $this->expectException(BookingException::class);

        $this->bookings->create($this->data(['check_out' => '2026-12-10']));
    }

    public function test_rejects_more_guests_than_the_room_takes(): void
    {
        $this->expectException(BookingException::class);

        $this->bookings->create($this->data(['adults' => 2, 'children' => 2]));
    }

    public function test_update_recalculates_total_and_ignores_its_own_dates(): void
    {
        $booking = $this->bookings->create($this->data());

        $this->bookings->update($booking, ['check_out' => '2026-12-14']);

        $this->assertSame(4 * 5500, $booking->fresh()->total_amount);
    }

    public function test_status_moves_through_the_stay(): void
    {
        $booking = $this->bookings->create($this->data());

        $this->bookings->confirm($booking);
        $this->bookings->checkIn($booking);
        $this->bookings->checkOut($booking);

        $this->assertSame(BookingStatus::CheckedOut, $booking->fresh()->status);
    }

    public function test_cannot_check_in_a_pending_booking(): void
    {
        $booking = $this->bookings->create($this->data());

        $this->expectException(BookingException::class);

        $this->bookings->checkIn($booking);
    }

    public function test_cancelled_booking_frees_the_room_and_cannot_be_reopened(): void
    {
        $booking = $this->bookings->create($this->data());
        $this->bookings->cancel($booking);

        $this->bookings->create($this->data());
        $this->assertSame(2, Booking::count());

        $this->expectException(BookingException::class);
        $this->bookings->confirm($booking);
    }

    public function test_quote_returns_null_until_room_and_dates_are_valid(): void
    {
        $this->assertNull($this->bookings->quote(null, '2026-12-10', '2026-12-12'));
        $this->assertNull($this->bookings->quote($this->room->id, '2026-12-12', '2026-12-10'));
        $this->assertSame(11000, $this->bookings->quote($this->room->id, '2026-12-10', '2026-12-12'));
    }

    private function data(array $overrides = []): array
    {
        return [
            'guest_id' => $this->guest->id,
            'room_id' => $this->room->id,
            'check_in' => '2026-12-10',
            'check_out' => '2026-12-12',
            'adults' => 2,
            'children' => 0,
            ...$overrides,
        ];
    }
}
