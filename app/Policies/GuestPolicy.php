<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;

/**
 * Staff manage guests; deleting is owner-only and only for guests without bookings.
 */
class GuestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Guest $guest): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Guest $guest): bool
    {
        return true;
    }

    public function delete(User $user, Guest $guest): bool
    {
        return $user->isOwner() && ! $guest->bookings()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
