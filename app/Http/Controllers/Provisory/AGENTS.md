# Provisory Controller Rules

Scope: `app/Http/Controllers/Provisory`.

## Structure

* Keep controllers directly in this directory; do not use `Pages`/`Invokes` folders or the `Page` suffix.
* Keep traits/properties and constructor at the top and imports grouped readably by type.
* When promoting a provisional screen, move its controller to `Public` or `Private` while preserving method signatures and conventions.

## Responsibilities

* Controllers orchestrate temporary Inertia flows; business actions belong to `app/Services` and validation to injected `app/Http/Requests`.
* Method parameter order: request, service, then route model.
* `show` actions return `InertiaRender` with the resource and required page props.
* Do not spread temporary business rules into models or resources.

## Inertia

* Page controllers must have `render()` as their last method.
* Build page props with private `index*` methods using the corresponding service, normally `filter()`.
* Reserve `show` for controller actions; never use `show*` as prop helpers.

## Queries

* Keep simple relation arrays/strings directly in `with()`/`load()`.
* Create private `*Relations` methods only for relations with query callbacks, chained logic or reuse across multiple queries.
