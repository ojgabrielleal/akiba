<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\FinishLocutionAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

class FinishLocutionController extends Controller
{
    use HasFlashMessages;

    public function __invoke(FinishLocutionAction $action)
    {
        $this->authorize('locution.finish');

        $action->execute();

        return $this->flashMessage('finish');
    }
}
