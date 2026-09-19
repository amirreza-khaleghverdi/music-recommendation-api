<?php

namespace App\Policies;

use App\Models\Artist;
use App\Models\User;
use App\Models\UserRole;

class ArtistPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Artist $artist): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Curator], true);
    }

    public function update(User $user, Artist $artist): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Curator], true);
    }

    public function delete(User $user, Artist $artist): bool
    {
        return $user->role === UserRole::Admin;
    }
}
