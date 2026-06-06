<?php

namespace App\Exceptions;

use App\Http\Controllers\Concerns\HasFlashMessages;
use Exception;

class RoleHasMembersException extends Exception
{
    use HasFlashMessages;

    /**
     * Renderiza a exceção em uma resposta HTTP.
     */
    public function render($request)
    {
        return $this->flashMessage('dependencies');
    }
}
