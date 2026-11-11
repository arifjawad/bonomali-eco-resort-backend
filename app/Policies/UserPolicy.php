<?php

namespace App\Policies;

use App\Models\User;

/**
 * Only the owner manages staff accounts.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    /**
     * The owner cannot delete their own account (and lock everyone out).
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isOwner() && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
