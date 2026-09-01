# Request Rules

Scope: `app/Http/Requests`.

* Keep requests in domain-scoped folders and name them by operation/domain.
* Web form requests must extend `LoggedWebRequest`, which centralizes shared authorization/validation logging.
* Every concrete request must implement `authorize(): bool` and `rules(): array`.
* Use Laravel policies via `$this->user()?->can(...)` for protected models; public/auth requests without model authorization may return `true`.
* Keep all input validation in requests, never controllers.
* Use `prepareForValidation()` to normalize input before validation.
* Validate nested arrays with dot notation and use conditional/nullable rules to represent conditional form behavior.
* Keep validation rules explicit and aligned with the payload structure.
* Do not duplicate validation-failure logging from `LoggedWebRequest`.
* Keep sensitive fields out of logs through `safeInputForLog()`.
