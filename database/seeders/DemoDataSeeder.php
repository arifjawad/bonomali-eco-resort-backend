<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\InquiryStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Inquiry;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Database\Seeder;

/**
 * Fake guests, bookings and inquiries so the admin has something to show.
 * Local only (see DatabaseSeeder). Bookings go through BookingService so
 * totals and availability are real.
 */
class DemoDataSeeder extends Seeder
{
    public function __construct(private BookingService $bookings) {}

    public function run(): void
    {
        if (Booking::exists()) {
            $this->command?->info('Demo bookings already exist: skipping demo data.');

            return;
        }

        $guests = Guest::factory()->count(15)->create();
        $rooms = Room::orderBy('name')->get();

        foreach ($this->stays() as $index => [$roomIndex, $startsInDays, $nights, $status]) {
            $checkIn = today()->addDays($startsInDays);

            $booking = $this->bookings->create([
                'guest_id' => $guests[$index % $guests->count()]->id,
                'room_id' => $rooms[$roomIndex % $rooms->count()]->id,
                'check_in' => $checkIn,
                'check_out' => $checkIn->copy()->addDays($nights),
                'adults' => 2,
                'children' => $index % 3 === 0 ? 1 : 0,
            ]);

            $this->moveTo($booking, $status);
        }

        Inquiry::factory()->count(4)->create();
        Inquiry::factory()->count(2)->create(['status' => InquiryStatus::Contacted]);
        Inquiry::factory()->create(['status' => InquiryStatus::Lost]);
    }

    /**
     * [room index, check-in offset from today in days, nights, status to end in].
     * Chosen so nothing overlaps on the same room.
     */
    private function stays(): array
    {
        return [
            [0, -40, 2, BookingStatus::CheckedOut],
            [1, -35, 3, BookingStatus::CheckedOut],
            [4, -20, 1, BookingStatus::CheckedOut],
            [8, -15, 2, BookingStatus::Cancelled],
            [2, -2, 2, BookingStatus::CheckedIn],   // departs today
            [3, -1, 3, BookingStatus::CheckedIn],   // in-house
            [5, -1, 2, BookingStatus::CheckedIn],   // in-house
            [0, 0, 2, BookingStatus::Confirmed],    // arrives today
            [6, 0, 1, BookingStatus::Confirmed],    // arrives today
            [1, 2, 2, BookingStatus::Confirmed],
            [9, 3, 3, BookingStatus::Pending],
            [4, 5, 2, BookingStatus::Confirmed],
            [7, 6, 1, BookingStatus::Pending],
            [2, 12, 2, BookingStatus::Confirmed],
            [8, 25, 3, BookingStatus::Pending],
            [0, 40, 2, BookingStatus::Confirmed],
        ];
    }

    private function moveTo(Booking $booking, BookingStatus $status): void
    {
        $steps = match ($status) {
            BookingStatus::Pending => [],
            BookingStatus::Confirmed => ['confirm'],
            BookingStatus::CheckedIn => ['confirm', 'checkIn'],
            BookingStatus::CheckedOut => ['confirm', 'checkIn', 'checkOut'],
            BookingStatus::Cancelled => ['cancel'],
        };

        foreach ($steps as $step) {
            $this->bookings->{$step}($booking);
        }
    }
}
