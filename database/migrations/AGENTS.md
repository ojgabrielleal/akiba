# Migration Rules

* Use Laravel schema abstractions (`Schema`, `Blueprint`, `foreignId`, `constrained`, indexes and equivalent helpers) for schema changes.
* Use `DB::` only for migrating existing data.
* Keep data migrations separate from schema migrations; never mix schema changes and data transformation in the same migration.
