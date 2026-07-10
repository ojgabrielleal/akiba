<?php

namespace App\Http\Controllers\Private\Radio\Program;

use App\Actions\Radio\Program\CreateProgramAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Radio\CreateProgramRequest;
use DomainException;

class StoreProgramController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateProgramRequest $request, CreateProgramAction $createProgramAction)
    {
        try {
            $createProgramAction->execute(
                $request->user(),
                $request->validated(),
                $request->file('image')
            );

            return $this->flashMessage('save');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }
}
