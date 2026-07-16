<?php

namespace App\Http\Controllers\Private\Profile\User;

use App\Actions\Profile\UpdateProfileAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;

class UpdateProfileController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateProfileRequest $request, User $user, UpdateProfileAction $updateProfileAction)
    {
        $updateProfileAction->execute(
            $user,
            $request->validated(),
            $request->file('avatar')
        );

        return $this->flashMessage('update');
    }
}
