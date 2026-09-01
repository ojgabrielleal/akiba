# Public Controller Rules

Scope: `app/Http/Controllers/Public`.

## Structure

* Keep controllers directly in this directory; do not use `Pages`/`Invokes` folders or the `Page` suffix.
* Name controllers after the screen or scope they represent.
* Keep traits/properties and constructor at the top; use constructor property promotion and group imports readably by type.

## Responsibilities

* Controllers orchestrate public pages/interactions; business actions belong to injected `app/Services` and validation to injected `app/Http/Requests`.
* Method parameter order: request, service, then route model.
* Use explicit action/scope method names.
* `show` actions return `InertiaRender` with the resource and required page props.
* Public player flows belong to `PlayerController`, not page controllers such as `RadioController`.
* Never put private/admin rules or inline validation in public controllers.

## Inertia

* Page controllers must have `render()` as their last method.
* Build page props with private `index*` methods using the corresponding service, normally `filter()`.
* Reserve `show` for controller actions; never use `show*` as prop helpers.
* Inline private methods that only return an array used once.

## Queries

* Keep simple relation arrays/strings directly in `with()`/`load()`.
* Create private `*Relations` methods only for relations with query callbacks, chained logic or reuse across multiple queries.
