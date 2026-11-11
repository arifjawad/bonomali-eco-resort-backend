<?php

namespace App\Policies;

use App\Models\RoomType;
use App\Models\User;

/**
 * Staff can see room types (they book them); only the owner changes them, since they hold prices.
 */
class RoomTypePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RoomType $roomType): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, RoomType $roomType): bool
    {
        return $user->isOwner();
    }

    public function reorder(User $user): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, RoomType $roomType): bool
    {
        return $user->isOwner() && ! $roomType->rooms()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
