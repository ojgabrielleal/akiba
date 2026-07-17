<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\User\DeactivateUserAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\User;

class DeactivateUserController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivateUserAction $deactivateUserAction,
    ) {}

    public function __invoke(User $user)
    {
        $this->authorize('deactivate', $user);

        $this->deactivateUserAction->execute($user);

        return $this->flashMessage('deactivate');
    }
}
