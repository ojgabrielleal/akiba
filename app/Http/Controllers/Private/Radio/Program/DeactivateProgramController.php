<?php

namespace App\Http\Controllers\Private\Radio\Program;

use App\Actions\Program\DeactivateProgramAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Program;

class DeactivateProgramController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Program $program, DeactivateProgramAction $deactivateProgramAction)
    {
        $this->authorize('delete', $program);

        $deactivateProgramAction->execute($program);

        return $this->flashMessage('deactivate');
    }
}
