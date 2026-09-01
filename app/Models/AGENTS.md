# Model Rules

Scope: `app/Models`.

## General

* Models handle persistence, casts, scopes, relationships and simple domain attributes only.
* Business flows belong to `app/Services`; page query composition belongs to the corresponding service, usually in `filter()`; reusable non-business processing belongs to `app/Processing`.

## Structure

* Keep models at the root of `app/Models` and shared model behavior in `app/Models/Concerns`.
* Use `HasFactory` when factories are needed.
* Models with UUID columns must use `HasUuids` and implement `uniqueIds(): array`, returning `['uuid']` when using the `uuid` column.

## Configuration

* Keep model configuration near the top, ordered as `$fillable`, `$hidden`, `$casts`.
* `$fillable`: mass-assignable fields.
* `$hidden`: internal foreign keys and sensitive values.
* `$casts`: booleans, arrays, dates and date/time formatting.

## Eloquent

* Use Eloquent `Attribute` objects for accessors/mutators.
* Keep reusable query logic in protected Laravel `#[Scope]` methods.
* Group relationships after scopes.
* Use explicit foreign keys when consistent with the existing project.
* Use expressive, domain-oriented relationship names.
