<?php

namespace App\Http\Controllers\Public;

use App\Actions\OAuthAccount\CompleteOAuthAccountProfileAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\OAuthAccount\CompleteOAuthAccountProfileRequest;

class OAuthAccountController extends Controller
{
    use HasFlashMessages;

    public function update(CompleteOAuthAccountProfileRequest $request, CompleteOAuthAccountProfileAction $action)
    {
        $action->execute(
            $request->attributes->get('oauth_account'),
            $request->validated(),
        );

        return $this->flashMessage('save', 'Perfil salvo com sucesso.');
    }
}
