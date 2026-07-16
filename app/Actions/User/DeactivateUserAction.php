<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeactivateUserAction
{
    public function execute(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->update(['is_active' => false]);

            return $user;
        });
    }
}
