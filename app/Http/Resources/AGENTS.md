#### Resource Organization Rules

1. Resources must stay in `app/Http/Resources`.

2. Resources with multiple related classes may use scope folders.
    - Examples: `Post`, `User`, `Poll`, `Program`, `Calendar`, `Onair`.

3. Resource classes must be named after the model or payload they transform, followed by `Resource`.
    - Examples: `PostResource`, `UserResource`, `ProgramAirtimeResource`.

4. Resource classes must extend `JsonResource`.

5. Resource collections that support presentation variants must use the `HasFormats` concern.

6. Use `format(?string $format)` for alternate resource shapes.
    - Examples: `summary`, `featured`, `home-list`, `grouped`, `history`.

7. Collection formatting must go through `FormattableResourceCollection`.
    - Do not duplicate collection-format handling in individual resources.

8. `toArray(Request $request): array` must return the default resource shape.

9. Format-specific shapes should be handled at the top of `toArray()`.
    - Example: return early when `$this->format === 'summary'`.

10. Use nested resources for relationships.
    - Examples: `UserResource::make($this->author)`, `PostTagResource::collection($this->tags)`.

11. Use `whenLoaded()` when a relation should only be serialized if it was eager-loaded.

12. Use small private methods for reusable or conditional payload fragments.
    - Examples: URL builders, current-user review payloads, grouped collection output.

13. Keep resources focused on response shape and presentation formatting.
    - Do not perform write operations in resources.
    - Avoid database queries in resources; eager-load relationships in controllers or filters.
