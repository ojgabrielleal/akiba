# Policy Rules

Scope: `app/Policies`.

* Keep policies at the root of `app/Policies`, named `{Model}Policy`, importing the protected model and `App\Models\User`.
* Authorization methods must return `bool`; use Laravel CRUD method names and explicit action names for non-CRUD permissions.
* Check permissions through `$user->hasPermission()` using the project's existing `module.action` permission keys.
* Keep policies thin and focused exclusively on authorization.
* Do not load services or perform database operations in policies.
* If authorization requires more than a simple permission check, move the domain logic elsewhere and keep only the authorization decision in the policy.
