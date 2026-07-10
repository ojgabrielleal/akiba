<?php

namespace App\Http\Controllers\Private\Administration\User;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\User;

class DeactivateUserController extends Controller
{
    use HasFlashMessages;

    public function __invoke(User $user)
    {
        $this->authorize('delete', $user);

        $user->update(['is_active' => false]);

        return $this->flashMessage('deactivate');
    }
}
