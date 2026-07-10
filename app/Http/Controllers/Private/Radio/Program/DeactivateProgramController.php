<?php

namespace App\Http\Controllers\Private\Radio\Program;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Program;

class DeactivateProgramController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Program $program)
    {
        $this->authorize('delete', $program);

        $program->update(['is_active' => false]);

        return $this->flashMessage('deactivate');
    }
}
