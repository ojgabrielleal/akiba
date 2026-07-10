<?php

namespace App\Http\Controllers\Private\Locution;

use App\Actions\Locution\FinishLocutionAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

class FinishLocutionController extends Controller
{
    use HasFlashMessages;

    public function __invoke(FinishLocutionAction $finishLocutionAction)
    {
        $this->authorize('locution.finish');

        $finishLocutionAction->execute();

        return $this->flashMessage('finish');
    }
}
