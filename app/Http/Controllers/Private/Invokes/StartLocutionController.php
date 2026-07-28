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

    public function __invoke(StartLocutionRequest $request, StartLocutionAction $action, Program $program)
    {
        $action->execute(
            $request->user(),
            $program,
            $request->all()
        );

        return $this->flashMessage('start');
    }
}
