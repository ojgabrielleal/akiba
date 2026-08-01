<?php

namespace App\Http\Controllers\Private;

use App\Actions\Program\StoreProgramAction;
use App\Actions\Program\UpdateProgramAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Program\StoreProgramRequest;
use App\Http\Requests\Program\UpdateProgramRequest;

use App\Models\Program;
use App\Models\User;

use DomainException;

class ProgramController extends Controller
{
    use HasFlashMessages;

    public function store(StoreProgramRequest $request, StoreProgramAction $action)
    {
        try {
            $action->execute(
                $request->user(),
                $this->responsible($request),
                $request->validated(),
                $request->file('image')
            );

            return $this->flashMessage('save');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function update(UpdateProgramRequest $request, UpdateProgramAction $action, Program $program)
    {
        try {
            $action->execute(
                $program,
                $request->user(),
                $this->responsible($request),
                $request->validated(),
                $request->file('image')
            );

            return $this->flashMessage('update');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    private function responsible(StoreProgramRequest|UpdateProgramRequest $request): User
    {
        if ($request->input('execution_mode') === 'auto_dj') {
            return $request->user();
        }

        if ($request->input('access_type') === 'free') {
            return $request->user();
        }

        return User::where('uuid', $request->input('user'))->firstOrFail();
    }
}
