<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Guests\GuestResource;
use App\Filament\Resources\Inquiries\InquiryResource;
use App\Filament\Resources\Rates\RateResource;
use App\Filament\Resources\Rooms\RoomResource;
use App\Filament\Resources\RoomTypes\RoomTypeResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Inquiry;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function resources(): array
    {
        return [
            'bookings' => [BookingResource::class],
            'inquiries' => [InquiryResource::class],
            'guests' => [GuestResource::class],
            'room types' => [RoomTypeResource::class],
            'rooms' => [RoomResource::class],
            'rates' => [RateResource::class],
            'users' => [UserResource::class],
        ];
    }

    #[DataProvider('resources')]
    public function test_owner_can_open_every_index_page(string $resource): void
    {
        $this->seedSomeData();

        $this->actingAs(User::factory()->owner()->create())
            ->get($resource::getUrl('index'))
            ->assertOk();
    }

    public function test_owner_can_open_booking_and_guest_forms(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->owner()->create());

        $this->get(BookingResource::getUrl('create'))->assertOk();
        $this->get(BookingResource::getUrl('edit', ['record' => $booking]))->assertOk();
        $this->get(BookingResource::getUrl('view', ['record' => $booking]))->assertOk();
        $this->get(GuestResource::getUrl('edit', ['record' => $booking->guest]))->assertOk();
    }

    public function test_staff_cannot_open_users_or_rates(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(UserResource::getUrl('index'))->assertForbidden();
        $this->get(RateResource::getUrl('index'))->assertForbidden();
    }

    public function test_staff_can_open_day_to_day_screens(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([BookingResource::class, InquiryResource::class, GuestResource::class, RoomResource::class, RoomTypeResource::class] as $resource) {
            $this->get($resource::getUrl('index'))->assertOk();
        }
    }

    public function test_staff_can_view_but_not_change_room_types(): void
    {
        $staff = User::factory()->create();
        $roomType = Rate::factory()->create()->roomType;

        $this->assertTrue($staff->can('viewAny', $roomType));
        $this->assertFalse($staff->can('update', $roomType));
        $this->assertFalse($staff->can('create', $roomType::class));
    }

    public function test_finished_bookings_are_read_only(): void
    {
        $owner = User::factory()->owner()->create();
        $booking = Booking::factory()->status(BookingStatus::CheckedOut)->create();

        $this->assertFalse($owner->can('update', $booking));
    }

    public function test_inactive_users_cannot_open_the_panel(): void
    {
        $this->actingAs(User::factory()->inactive()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_owner_cannot_delete_themselves(): void
    {
        $owner = User::factory()->owner()->create();

        $this->assertFalse($owner->can('delete', $owner));
        $this->assertTrue($owner->can('delete', User::factory()->create()));
    }

    private function seedSomeData(): void
    {
        Booking::factory()->count(2)->create();
        Inquiry::factory()->count(2)->create();
        Guest::factory()->create();
        Rate::factory()->create();
    }
}
