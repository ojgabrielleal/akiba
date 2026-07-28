#### Request Organization Rules

1. Request classes must stay in scope folders inside `app/Http/Requests`.
    - Examples: `Post/StorePostRequest.php`, `Program/UpdateProgramRequest.php`.

2. Shared request behavior must stay in `LoggedWebRequest`.

3. Web form requests must extend `LoggedWebRequest`.

4. Request class names must describe the operation and module.
    - Examples: `StorePostRequest`, `UpdateProgramRequest`, `AuthLoginRequest`.

5. Each concrete request must define `authorize(): bool`.

6. Authorization must use Laravel policies through `$this->user()?->can(...)` when the request is tied to a protected model.
    - Example: `$this->user()?->can('create', Post::class) ?? false`.
    - Example: `$this->user()?->can('update', $this->route('post')) ?? false`.

7. Public or authentication requests that do not require a model policy may return `true` from `authorize()`.

8. Each concrete request must define `rules(): array`.

9. Validation rules must stay inside the request class, not inside controllers.

10. Use `prepareForValidation()` when incoming data needs to be normalized before rules run.
    - Example: merging a route model value into request data before validation.

11. Keep request rules explicit and close to the payload shape.
    - Nested arrays should validate their nested fields with dot notation.
    - Examples: `references.*.name`, `tags.*.uuid`, `metadata.address`.

12. Use `required_if`, `required_unless`, `required_with`, and nullable rules to model conditional form behavior.

13. `LoggedWebRequest` must keep validation and authorization logging centralized.
    - Do not duplicate failed validation logging in concrete requests.
    - Keep sensitive fields out of logs through `safeInputForLog()`.
