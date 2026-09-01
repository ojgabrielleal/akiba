# Private Controller Rules

Scope: `app/Http/Controllers/Private`.

## Structure

* Keep controllers directly in this directory; do not use `Pages`/`Invokes` folders or the `Page` suffix.
* Name controllers after the screen or scope they represent.
* Keep traits/properties and constructor at the top; use constructor property promotion.
* Import dependencies with `use`, grouped readably by type.

## Responsibilities

* Controllers orchestrate requests, authorization, services, resources and private Inertia rendering.
* Business actions belong to injected `app/Services`; validation belongs to injected `app/Http/Requests`.
* Method parameter order: request, service, then route model.
* Authorize every action; when a FormRequest validates the action, authorization belongs there.
* Use explicit action/scope method names.
* `show` actions return `InertiaRender` with the resource and required page props.
* Never put extensive business logic or inline validation in controllers.

## Inertia

* Page controllers must have `render()` as their last method.
* Build page props with private `index*` methods using the corresponding service, normally `filter()`.
* Reserve `show` for controller actions; never use `show*` as prop helpers.
* Inline private methods that only return an array used once.

## Queries

* Keep simple relation arrays/strings directly in `with()`/`load()`.
* Create private `*Relations` methods only for relations with query callbacks, chained logic or reuse across multiple queries.
