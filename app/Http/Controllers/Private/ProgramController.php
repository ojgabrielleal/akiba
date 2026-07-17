<?php

namespace App\Http\Controllers\Private;

use App\Actions\Program\StoreProgramAction;
use App\Actions\Program\UpdateProgramAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Program\StoreProgramRequest;
use App\Http\Requests\Program\UpdateProgramRequest;

use App\Models\Program;

use DomainException;

class ProgramController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreProgramAction $storeProgramAction,
        private UpdateProgramAction $updateProgramAction,
    ) {}

    public function store(StoreProgramRequest $request)
    {
        try {
            $this->storeProgramAction->execute(
                $request->user(),
                $request->validated(),
                $request->file('image')
            );

            return $this->flashMessage('save');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function update(UpdateProgramRequest $request, Program $program)
    {
        try {
            $this->updateProgramAction->execute(
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
