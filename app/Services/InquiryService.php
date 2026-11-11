<?php

namespace App\Services;

use App\Enums\InquiryStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InquiryService
{
    public function __construct(private BookingService $bookings) {}

    /**
     * Turn an open inquiry into a booking. The guest is matched by phone number,
     * or created from the inquiry when they are new.
     *
     * @param  array  $bookingData  room_id plus any booking fields to override (dates, adults, children, notes)
     */
    public function convertToBooking(Inquiry $inquiry, array $bookingData, ?User $by = null): Booking
    {
        if (! $inquiry->status->isOpen()) {
            throw BookingException::inquiryClosed($inquiry->status);
        }

        return DB::transaction(function () use ($inquiry, $bookingData, $by) {
            $guest = $this->findOrCreateGuest($inquiry);

            $booking = $this->bookings->create([
                'check_in' => $inquiry->check_in,
                'check_out' => $inquiry->check_out,
                'adults' => $inquiry->guests ?? 1,
                'notes' => $inquiry->message,
                'source' => $inquiry->source,
                ...array_filter($bookingData, fn ($value) => $value !== null),
                'guest_id' => $guest->id,
            ], $by);

            $inquiry->update([
                'status' => InquiryStatus::Converted,
                'booking_id' => $booking->id,
            ]);

            return $booking;
        });
    }

    private function findOrCreateGuest(Inquiry $inquiry): Guest
    {
        return Guest::firstOrCreate(
            ['phone' => $inquiry->phone],
            ['name' => $inquiry->name, 'source' => $inquiry->source, 'tags' => []],
        );
    }
}
