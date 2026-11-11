<?php

namespace Tests\Feature\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availability;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availability = app(AvailabilityService::class);
        $this->room = Room::factory()->create();

        // Existing stay: nights of 10th and 11th, leaves on the 12th.
        Booking::factory()->for($this->room)->create(['check_in' => '2026-12-10', 'check_out' => '2026-12-12']);
    }

    public static function stays(): array
    {
        return [
            'same dates' => ['2026-12-10', '2026-12-12', false],
            'starts inside' => ['2026-12-11', '2026-12-14', false],
            'ends inside' => ['2026-12-08', '2026-12-11', false],
            'wraps around' => ['2026-12-09', '2026-12-13', false],
            'arrives on their check-out day' => ['2026-12-12', '2026-12-14', true],
            'leaves on their check-in day' => ['2026-12-08', '2026-12-10', true],
            'well before' => ['2026-12-01', '2026-12-03', true],
        ];
    }

    #[DataProvider('stays')]
    public function test_room_is_free_only_when_nights_do_not_overlap(string $checkIn, string $checkOut, bool $free): void
    {
        $this->assertSame($free, $this->isFree($checkIn, $checkOut));
    }

    public function test_cancelled_bookings_do_not_block_the_room(): void
    {
        Booking::query()->update(['status' => BookingStatus::Cancelled]);

        $this->assertTrue($this->isFree('2026-12-10', '2026-12-12'));
    }

    public function test_other_rooms_do_not_block(): void
    {
        $otherRoom = Room::factory()->create();

        $this->assertTrue($this->availability->isRoomFree(
            $otherRoom, CarbonImmutable::parse('2026-12-10'), CarbonImmutable::parse('2026-12-12'),
        ));
    }

    public function test_a_booking_does_not_clash_with_itself_when_edited(): void
    {
        $booking = Booking::first();

        $this->assertTrue($this->availability->isRoomFree(
            $this->room, CarbonImmutable::parse('2026-12-10'), CarbonImmutable::parse('2026-12-13'), ignore: $booking,
        ));
    }

    public function test_occupancy_percent_counts_rooms_with_a_guest_that_night(): void
    {
        Room::factory()->create(); // 2 active rooms, 1 booked on the 10th

        $this->assertSame(50, $this->availability->occupancyPercent(CarbonImmutable::parse('2026-12-10')));
        $this->assertSame(0, $this->availability->occupancyPercent(CarbonImmutable::parse('2026-12-12')));
    }

    private function isFree(string $checkIn, string $checkOut): bool
    {
        return $this->availability->isRoomFree(
            $this->room, CarbonImmutable::parse($checkIn), CarbonImmutable::parse($checkOut),
        );
    }
}
