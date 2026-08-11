<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Services\FormSubmissionService;
use App\Http\Requests\FormSubmission\StoreFormSubmissionRequest;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{

    public function storeFormSubmission(StoreFormSubmissionRequest $request, FormSubmissionService $service): RedirectResponse
    {
        $service->store($request->validated());

        return back(303)->with('success', 'Formulário enviado com sucesso.');
    }

    public function render()
    {
        return Inertia::render('public/Contact');
    }
}
