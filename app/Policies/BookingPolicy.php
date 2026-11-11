<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

/**
 * Staff create and edit bookings. Finished (checked out / cancelled) bookings
 * are read-only; staff cancel instead of deleting, deleting is owner-only.
 */
class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Booking $booking): bool
    {
        return ! $booking->status->isFinal();
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->isOwner();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isOwner();
    }
}
