# Resource Rules

Scope: `app/Http/Resources`.

* Resources handle response formatting and presentation for API/Inertia, extend `JsonResource`, and use the `{Payload}Resource` naming pattern.
* Keep resources in `app/Http/Resources`; related resources may use domain-scoped folders.
* Collections with presentation variants must use `HasFormats`, `format(?string $format)` and `FormattableResourceCollection`; never duplicate collection-format handling in individual resources.
* `toArray(Request $request): array` returns the default format; handle specific formats at the top with early returns.
* Use nested resources for relationships and `whenLoaded()` when serialization depends on eager loading.
* Use small private helpers for reusable or conditional payload fragments.
* Keep resources presentation-only: never perform writes or database queries; eager-load relationships in controllers/services.
