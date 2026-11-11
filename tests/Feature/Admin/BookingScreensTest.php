<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\InquiryStatus;
use App\Enums\Source;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Inquiries\Pages\ManageInquiries;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Inquiry;
use App\Models\Room;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The admin screens call the services; the services' own rules are tested in tests/Feature/Services.
 */
class BookingScreensTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_staff_create_a_booking_from_the_form(): void
    {
        $room = Room::factory()->create();
        $guest = Guest::factory()->create();

        Livewire::test(CreateBooking::class)
            ->fillForm([
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'check_in' => '2026-12-10',
                'check_out' => '2026-12-12',
                'adults' => 2,
                'children' => 0,
                'source' => Source::WhatsApp,
            ])
            ->assertSee(number_format(2 * $room->roomType->base_price).' BDT')
            ->call('create')
            ->assertHasNoFormErrors();

        $booking = Booking::sole();
        $this->assertSame(2 * $room->roomType->base_price, $booking->total_amount);
        $this->assertSame(auth()->id(), $booking->created_by);
    }

    public function test_double_booking_from_the_form_is_refused(): void
    {
        $existing = Booking::factory()->create(['check_in' => '2026-12-10', 'check_out' => '2026-12-12']);

        Livewire::test(CreateBooking::class)
            ->fillForm([
                'guest_id' => Guest::factory()->create()->id,
                'room_id' => $existing->room_id,
                'check_in' => '2026-12-11',
                'check_out' => '2026-12-13',
                'adults' => 2,
                'children' => 0,
                'source' => Source::Phone,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertSame(1, Booking::count());
    }

    public function test_confirm_action_moves_a_pending_booking(): void
    {
        $booking = Booking::factory()->status(BookingStatus::Pending)->create();

        Livewire::test(ListBookings::class)
            ->callAction(TestAction::make('confirm')->table($booking));

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_check_in_action_is_hidden_for_pending_bookings(): void
    {
        $booking = Booking::factory()->status(BookingStatus::Pending)->create();

        Livewire::test(ListBookings::class)
            ->assertActionHidden(TestAction::make('checkIn')->table($booking));
    }

    public function test_convert_action_turns_an_inquiry_into_a_booking(): void
    {
        $inquiry = Inquiry::factory()->create(['check_in' => '2026-12-10', 'check_out' => '2026-12-12', 'guests' => 2]);
        $room = Room::factory()->create();

        Livewire::test(ManageInquiries::class)
            ->callAction(TestAction::make('convert')->table($inquiry), data: ['room_id' => $room->id])
            ->assertHasNoFormErrors();

        $this->assertSame(InquiryStatus::Converted, $inquiry->fresh()->status);
        $this->assertNotNull($inquiry->fresh()->booking_id);
    }
}
