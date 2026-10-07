<?php

namespace App\Policies;

use App\Models\RadioStation;
use App\Models\User;

class RadioStationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('report.module.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('report.module.view');
    }

    public function update(User $user, RadioStation $radioStation): bool
    {
        return $user->hasPermission('report.module.view');
    }

    public function delete(User $user, RadioStation $radioStation): bool
    {
        return $user->hasPermission('report.module.view');
    }
}
