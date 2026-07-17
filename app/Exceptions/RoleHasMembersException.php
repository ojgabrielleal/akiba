<?php

namespace App\Exceptions;

use Exception;

class RoleHasMembersException extends Exception
{
    public function __construct()
    {
        parent::__construct('Tire os vínculos antes! Senão dá ruim.');
    }
}
