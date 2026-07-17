<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Program\DeactivateProgramAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Program;

class DeactivateProgramController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivateProgramAction $deactivateProgramAction,
    ) {}

    public function __invoke(Program $program)
    {
        $this->authorize('deactivate', $program);

        $this->deactivateProgramAction->execute($program);

        return $this->flashMessage('deactivate');
    }
}
