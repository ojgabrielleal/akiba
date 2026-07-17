<?php

namespace App\Actions\Program;

use App\Models\Program;

use Illuminate\Support\Facades\DB;

class DeactivateProgramAction
{
    public function execute(Program $program): Program
    {
        return DB::transaction(function () use ($program) {
            $program->update(['is_active' => false]);

            return $program;
        });
    }
}
