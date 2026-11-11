<?php

namespace App\Policies;

use App\Models\Rate;
use App\Models\User;

/**
 * Seasonal prices are owner-only.
 */
class RatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function view(User $user, Rate $rate): bool
    {
        return $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Rate $rate): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, Rate $rate): bool
    {
        return $user->isOwner();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isOwner();
    }
}
