<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonInterface;

/**
 * Which rooms are free. A room is taken on a night when a booking that is not
 * cancelled covers it; the check-out day is free for the next guest.
 */
class AvailabilityService
{
    /**
     * @param  Booking|null  $ignore  the booking being edited, so it does not clash with itself
     */
    public function isRoomFree(Room $room, CarbonInterface $checkIn, CarbonInterface $checkOut, ?Booking $ignore = null): bool
    {
        return ! $room->bookings()
            ->notCancelled()
            ->overlapping($checkIn, $checkOut)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists();
    }

    /**
     * Share of active rooms with a guest staying on the night of $night, 0-100.
     */
    public function occupancyPercent(CarbonInterface $night): int
    {
        $activeRooms = Room::where('is_active', true)->count();

        if ($activeRooms === 0) {
            return 0;
        }

        $occupiedRooms = Booking::notCancelled()
            ->staying($night)
            ->distinct()
            ->count('room_id');

        return (int) round($occupiedRooms / $activeRooms * 100);
    }
}
