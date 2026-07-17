<?php

namespace App\Http\Controllers\Concerns;

use Closure;

use Illuminate\Support\Facades\Gate;

trait ResolvesAuthorizedProps
{
    protected function whenCanViewAny(string $model, Closure $resolve): mixed 
    {
        return Gate::allows('viewAny', $model) ? $resolve() : null;
    }
}
