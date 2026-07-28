#### Policy Organization Rules

1. Policies must stay in the root of `app/Policies`.

2. Each policy must be named after the model it protects, followed by `Policy`.
    - Example: `PostPolicy`, `ProgramPolicy`, `UserPolicy`.

3. Policies must import the protected model and `App\Models\User` with `use` statements before the class declaration.

4. Authorization methods must return `bool`.

5. Standard CRUD policy methods must follow Laravel naming.
    - `viewAny(User $user): bool`
    - `view(User $user, Model $model): bool`
    - `create(User $user): bool`
    - `update(User $user, Model $model): bool`
    - `delete(User $user, Model $model): bool`

6. Non-CRUD permissions must use explicit method names that describe the action.
    - Examples: `deactivate`, `approve`, `vote`, `refreshRanking`, `toggleBoxStatus`.

7. Permission checks must go through the user permission helper.
    - Example: `$user->hasPermission('post.create')`.

8. Permission keys must follow the module-action pattern already used in the project.
    - Examples: `post.list`, `program.update`, `poll.vote`.

9. Keep policies thin.
    - Do not load extra services or run database operations inside policies.
    - If a rule grows beyond a simple permission check, move domain logic elsewhere and keep the policy focused on authorization.
