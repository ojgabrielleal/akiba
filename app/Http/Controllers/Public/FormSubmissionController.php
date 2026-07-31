<?php

namespace App\Http\Controllers\Public;

use App\Actions\FormSubmission\StoreFormSubmissionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\FormSubmission\StoreFormSubmissionRequest;
use Illuminate\Http\RedirectResponse;

class FormSubmissionController extends Controller
{
    public function store(StoreFormSubmissionRequest $request, StoreFormSubmissionAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return back(303)->with('success', 'Formulário enviado com sucesso.');
    }
}
