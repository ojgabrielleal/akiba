<?php

namespace App\Policies;

use App\Models\SongRequestBlock;
use App\Models\User;

class SongRequestBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('audience.moderation.list');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('audience.moderation.block');
    }

    public function delete(User $user, SongRequestBlock $songRequestBlock): bool
    {
        return $user->hasPermission('audience.moderation.unblock');
    }
}
