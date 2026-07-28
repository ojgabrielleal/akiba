#### Model Organization Rules

1. Models must stay in the root of `app/Models`.

2. Shared model behavior must stay in `app/Models/Concerns`.
    - Example: `HasPermissions`.

3. Models that need factories must use `HasFactory`.

4. Models with UUID columns must use `HasUuids` and define `uniqueIds(): array`.
    - The method must return `['uuid']` when the model uses the `uuid` column.

5. Keep model configuration near the top of the class.
    - `$fillable`
    - `$hidden`
    - `$casts`

6. Use `$fillable` for fields accepted by mass assignment.

7. Use `$hidden` for internal foreign keys and sensitive values.
    - Examples: `user_id`, `activity_id`, `password`, `remember_token`.

8. Use `$casts` for booleans, arrays, dates, and time/date formatting.
    - Examples: `is_active`, `is_virtual`, `metadata`, `phrases`, `birth_date`.

9. Use Eloquent `Attribute` objects for accessors and mutators.
    - Examples: password hashing, slug generation from title or nickname.

10. Query scopes must use Laravel's `#[Scope]` attribute and be protected methods.
    - Example: `protected function active(Builder $query): void`.

11. Keep reusable query logic in model scopes.
    - Examples: `active`, `upcoming`, `withStatus`, `authoredBy`, `forModule`, `availableForLocution`.

12. Relationship methods must stay grouped after scopes.

13. Relationship methods should use explicit foreign keys when the project already does so.
    - Examples: `hasMany(Post::class, 'user_id')`, `belongsTo(User::class, 'user_id')`.

14. Prefer expressive relationship names that match the domain.
    - Examples: `author`, `host`, `responsible`, `favorites`, `reviews`, `programAirtimes`.

15. Keep models focused on persistence, casting, scopes, relationships, and simple attributes.
    - Business workflows belong in `app/Actions`.
    - Query composition for pages belongs in `app/Filters`.
