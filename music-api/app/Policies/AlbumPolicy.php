<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;
use App\Models\UserRole;

class AlbumPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Album $album): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Curator], true);
    }

    public function update(User $user, Album $album): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Curator], true);
    }

    public function delete(User $user, Album $album): bool
    {
        return $user->role === UserRole::Admin;
    }
}
