<?php

namespace App\Http\Controllers\Private;

use App\Actions\Profile\UpdateProfileAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Profile\UpdateProfileRequest;

use App\Models\User;

class ProfileController extends Controller
{
    use HasFlashMessages;

    public function update(UpdateProfileRequest $request, UpdateProfileAction $action, User $user)
    {
        $action->execute(
            $user,
            $request->validated(),
            $request->file('avatar')
        );

        return $this->flashMessage('update');
    }

}
