# Service Rules

Scope: `app/Services`.

## Structure

* Keep services directly in `app/Services`, one per domain scope; do not create subfolders, `Actions` or `Filters`.
* Keep properties/constructor at the top and use constructor property promotion.
* Keep public behavior methods before their related private helpers.

## Responsibilities

* Services own business rules, domain operations and scoped query composition.
* Use clear action names for writes and `filter(array $filters = [])` for query/list composition.
* Use `DB::transaction()` when operations affect multiple tables or require consistency.
* Put reusable non-business processing in `app/Processing` and external APIs, webhooks, streams, providers and SDKs in `app/Integrations`.
* Do not access requests directly when data can be passed as parameters.
* Never return HTTP/Inertia responses from services.

## Helpers

* Prefix private helpers with their parent flow when it improves clarity.
* Inline private helpers that only return an array used once.
