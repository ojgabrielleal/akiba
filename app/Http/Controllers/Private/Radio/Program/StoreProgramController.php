<?php

namespace App\Http\Controllers\Private\Radio\Program;

use App\Actions\Program\StoreProgramAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Program\StoreProgramRequest;
use DomainException;

class StoreProgramController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreProgramRequest $request, StoreProgramAction $storeProgramAction)
    {
        try {
            $storeProgramAction->execute(
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
