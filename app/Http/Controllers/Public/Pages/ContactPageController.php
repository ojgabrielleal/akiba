<?php

namespace App\Http\Controllers\Public\Pages;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ContactPageController extends Controller
{
    public function render()
    {
        return Inertia::render('public/Contact');
    }
}
