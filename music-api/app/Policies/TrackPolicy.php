<?php

namespace App\Policies;

use App\Models\Track;
use App\Models\User;
use App\Models\UserRole;

class TrackPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Track $track): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Curator], true);
    }

    public function update(User $user, Track $track): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Curator], true);
    }

    public function delete(User $user, Track $track): bool
    {
        return $user->role === UserRole::Admin;
    }
}
