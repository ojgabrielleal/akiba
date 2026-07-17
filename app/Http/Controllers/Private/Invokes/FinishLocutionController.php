<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\FinishLocutionAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

class FinishLocutionController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private FinishLocutionAction $finishLocutionAction,
    ) {}

    public function __invoke()
    {
        $this->authorize('locution.finish');

        $this->finishLocutionAction->execute();

        return $this->flashMessage('finish');
    }
}
