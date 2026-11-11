<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

/**
 * Staff manage rooms; deleting is owner-only and only for rooms never booked.
 */
class RoomPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Room $room): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Room $room): bool
    {
        return true;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->isOwner() && ! $room->bookings()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
