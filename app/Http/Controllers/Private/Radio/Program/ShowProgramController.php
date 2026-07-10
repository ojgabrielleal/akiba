<?php

namespace App\Http\Controllers\Private\Radio\Program;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Program;

class ShowProgramController extends Controller
{
    public function __invoke(Program $program)
    {
        $this->authorize('view', $program);

        return new ProgramResource(
            $program->load([
                'host',
                'airtimes',
                'plans' => fn ($query) => $query->unexecuted()->orderBy('scheduled_at'),
                'plans.user',
            ])
        );
    }
}
