<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\User\DeactivateUserAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\User;

class DeactivateUserController extends Controller
{
    use HasFlashMessages;

    public function __invoke(DeactivateUserAction $action, User $user)
    {
        $this->authorize('deactivate', $user);

        $action->execute($user);

        return $this->flashMessage('deactivate');
    }
}
