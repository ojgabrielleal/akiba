<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

abstract class LoggedWebRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        Log::warning("[REQUEST VALIDATION FAILED] {$this->requestName()}", [
            ...$this->logContext(),
            'errors' => $validator->errors()->toArray(),
        ]);

        parent::failedValidation($validator);
    }

    protected function failedAuthorization(): void
    {
        Log::warning("[REQUEST UNAUTHORIZED] {$this->requestName()}", $this->logContext());

        parent::failedAuthorization();
    }

    /**
     * @return array<string, mixed>
     */
    protected function logContext(): array
    {
        $route = $this->route();

        return [
            'request' => static::class,
            'request_name' => $this->requestName(),
            'user_id' => $this->user()?->getKey(),
            'method' => $this->method(),
            'path' => $this->path(),
            'route' => $route?->getName(),
            'action' => $route?->getActionName(),
            'input_fields' => array_keys($this->safeInputForLog()),
            'file_fields' => array_keys($this->allFiles()),
        ];
    }

    protected function requestName(): string
    {
        return class_basename(static::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function safeInputForLog(): array
    {
        return $this->except([
            'password',
            'password_confirmation',
            'current_password',
            'token',
            '_token',
        ]);
    }
}
