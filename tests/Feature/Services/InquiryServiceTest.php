<?php

namespace Tests\Feature\Services;

use App\Enums\InquiryStatus;
use App\Enums\Source;
use App\Exceptions\BookingException;
use App\Models\Guest;
use App\Models\Inquiry;
use App\Models\Room;
use App\Services\InquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_converts_an_inquiry_into_a_new_guest_and_booking(): void
    {
        $room = Room::factory()->create();
        $inquiry = Inquiry::factory()->create([
            'phone' => '+8801711000000',
            'check_in' => '2026-12-10',
            'check_out' => '2026-12-12',
            'guests' => 2,
            'source' => Source::Facebook,
        ]);

        $booking = app(InquiryService::class)->convertToBooking($inquiry, ['room_id' => $room->id]);

        $inquiry->refresh();
        $this->assertSame(InquiryStatus::Converted, $inquiry->status);
        $this->assertSame($booking->id, $inquiry->booking_id);
        $this->assertSame('+8801711000000', $booking->guest->phone);
        $this->assertSame(Source::Facebook, $booking->source);
        $this->assertSame('2026-12-10', $booking->check_in->toDateString());
        $this->assertSame(2, $booking->adults);
    }

    public function test_reuses_an_existing_guest_with_the_same_phone(): void
    {
        $guest = Guest::factory()->create(['phone' => '+8801711000000']);
        $inquiry = Inquiry::factory()->create(['phone' => '+8801711000000', 'guests' => 2]);

        $booking = app(InquiryService::class)->convertToBooking($inquiry, ['room_id' => Room::factory()->create()->id]);

        $this->assertSame($guest->id, $booking->guest_id);
        $this->assertSame(1, Guest::count());
    }

    public function test_closed_inquiries_cannot_be_converted(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => InquiryStatus::Lost]);

        $this->expectException(BookingException::class);

        app(InquiryService::class)->convertToBooking($inquiry, ['room_id' => Room::factory()->create()->id]);
    }

    public function test_nothing_is_saved_when_the_room_is_taken(): void
    {
        $room = Room::factory()->create();
        $first = Inquiry::factory()->create(['check_in' => '2026-12-10', 'check_out' => '2026-12-12', 'guests' => 2]);
        $second = Inquiry::factory()->create(['check_in' => '2026-12-11', 'check_out' => '2026-12-12', 'guests' => 2]);
        app(InquiryService::class)->convertToBooking($first, ['room_id' => $room->id]);

        try {
            app(InquiryService::class)->convertToBooking($second, ['room_id' => $room->id]);
            $this->fail('Expected the room to be taken.');
        } catch (BookingException) {
            $this->assertSame(InquiryStatus::New, $second->fresh()->status);
            $this->assertSame(1, Guest::count(), 'The second guest should be rolled back.');
        }
    }
}
