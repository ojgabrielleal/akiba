<?php

namespace App\Http\Controllers\Public\Pages;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function render()
    {
        return Inertia::render('public/Home');
    }
}
