<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\Source;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Manual bookings entered by staff: create, edit and move through check-in/out.
 *
 * $data keys: guest_id, room_id, check_in, check_out, adults, children, source, notes.
 * Dates may be strings ("2026-12-01") or Carbon instances.
 */
class BookingService
{
    public function __construct(
        private PricingService $pricing,
        private AvailabilityService $availability,
    ) {}

    public function create(array $data, ?User $createdBy = null): Booking
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $room = Room::with('roomType')->findOrFail($data['room_id']);
            [$checkIn, $checkOut] = $this->parseDates($data);

            $this->ensureBookable($room, $checkIn, $checkOut, $data);

            return Booking::create([
                ...$this->bookingFields($data, $room, $checkIn, $checkOut),
                'reference' => $this->nextReference(),
                'status' => BookingStatus::Pending,
                'source' => $data['source'] ?? Guest::find($data['guest_id'])?->source ?? Source::Other,
                'created_by' => $createdBy?->getKey(),
            ]);
        });
    }

    /**
     * Change dates, room, guests or notes. The total is recalculated.
     */
    public function update(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $data = [...$booking->only(['guest_id', 'room_id', 'check_in', 'check_out', 'adults', 'children', 'notes']), ...$data];
            $room = Room::with('roomType')->findOrFail($data['room_id']);
            [$checkIn, $checkOut] = $this->parseDates($data);

            $this->ensureBookable($room, $checkIn, $checkOut, $data, ignore: $booking);

            $booking->update([
                ...$this->bookingFields($data, $room, $checkIn, $checkOut),
                'source' => $data['source'] ?? $booking->source,
            ]);

            return $booking;
        });
    }

    /**
     * Price for the live preview on the booking form; null until room and dates are valid.
     */
    public function quote(?int $roomId, mixed $checkIn, mixed $checkOut): ?int
    {
        if (! $roomId || blank($checkIn) || blank($checkOut)) {
            return null;
        }

        $room = Room::with('roomType')->find($roomId);
        [$checkIn, $checkOut] = [CarbonImmutable::parse($checkIn), CarbonImmutable::parse($checkOut)];

        if (! $room || $checkOut->lte($checkIn)) {
            return null;
        }

        return $this->pricing->stayTotal($room->roomType, $checkIn, $checkOut);
    }

    public function confirm(Booking $booking): Booking
    {
        return $this->moveTo($booking, BookingStatus::Confirmed);
    }

    public function checkIn(Booking $booking): Booking
    {
        return $this->moveTo($booking, BookingStatus::CheckedIn);
    }

    public function checkOut(Booking $booking): Booking
    {
        return $this->moveTo($booking, BookingStatus::CheckedOut);
    }

    public function cancel(Booking $booking): Booking
    {
        return $this->moveTo($booking, BookingStatus::Cancelled);
    }

    private function moveTo(Booking $booking, BookingStatus $status): Booking
    {
        if (! $booking->status->canMoveTo($status)) {
            throw BookingException::cannotMove($booking->status, $status);
        }

        $booking->update(['status' => $status]);

        return $booking;
    }

    private function ensureBookable(Room $room, CarbonImmutable $checkIn, CarbonImmutable $checkOut, array $data, ?Booking $ignore = null): void
    {
        if ($checkOut->lte($checkIn)) {
            throw BookingException::invalidDates();
        }

        if (! $room->is_active) {
            throw BookingException::roomInactive();
        }

        $guestCount = (int) ($data['adults'] ?? 1) + (int) ($data['children'] ?? 0);
        if ($guestCount > $room->roomType->max_guests) {
            throw BookingException::tooManyGuests($room->roomType->max_guests);
        }

        if (! $this->availability->isRoomFree($room, $checkIn, $checkOut, $ignore)) {
            throw BookingException::roomNotFree($room->name);
        }
    }

    private function bookingFields(array $data, Room $room, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        return [
            'guest_id' => $data['guest_id'],
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => $data['adults'] ?? 1,
            'children' => $data['children'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'total_amount' => $this->pricing->stayTotal($room->roomType, $checkIn, $checkOut),
        ];
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function parseDates(array $data): array
    {
        if (blank($data['check_in'] ?? null) || blank($data['check_out'] ?? null)) {
            throw BookingException::missingDates();
        }

        return [
            CarbonImmutable::parse($data['check_in'])->startOfDay(),
            CarbonImmutable::parse($data['check_out'])->startOfDay(),
        ];
    }

    /**
     * BNM-<year>-<running number>, restarting each year: BNM-2026-0001.
     */
    private function nextReference(): string
    {
        $prefix = 'BNM-'.now()->year.'-';

        $last = Booking::where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
