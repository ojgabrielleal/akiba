<?php

namespace App\Http\Controllers\Private\Radio\Program;

use App\Actions\Radio\Program\UpdateProgramAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Radio\UpdateProgramRequest;
use App\Models\Program;
use DomainException;

class UpdateProgramController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateProgramRequest $request, UpdateProgramAction $updateProgramAction, Program $program)
    {
        try {
            $updateProgramAction->execute(
                $program,
                $request->user(),
                $request->validated(),
                $request->file('image')
            );

            return $this->flashMessage('update');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }
}
