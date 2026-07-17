<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\StartLocutionAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Locution\StartLocutionRequest;

use App\Models\Program;

class StartLocutionController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StartLocutionAction $startLocutionAction,
    ) {}

    public function __invoke(StartLocutionRequest $request, Program $program)
    {
        $this->startLocutionAction->execute(
            $request->user(),
            $program,
            $request->all()
        );

        return $this->flashMessage('start');
    }
}
