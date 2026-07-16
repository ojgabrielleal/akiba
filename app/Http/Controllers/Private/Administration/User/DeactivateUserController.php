<?php

namespace App\Http\Controllers\Private\Administration\User;

use App\Actions\User\DeactivateUserAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\User;

class DeactivateUserController extends Controller
{
    use HasFlashMessages;

    public function __invoke(User $user, DeactivateUserAction $deactivateUserAction)
    {
        $this->authorize('delete', $user);

        $deactivateUserAction->execute($user);

        return $this->flashMessage('deactivate');
    }
}
