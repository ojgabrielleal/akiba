<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class LoginPageController extends Controller
{
    private $render = 'private/Login';

    public function render()
    {
        return Inertia::render($this->render);
    }
}
