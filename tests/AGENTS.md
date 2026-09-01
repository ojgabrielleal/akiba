# Test Rules

Scope: `tests/`.

## General

* Follow idiomatic Laravel testing with `Tests\TestCase`, framework helpers and expressive assertions.
* Test observable behavior, keep each test focused, and use descriptive `snake_case` names.
* Prefer factories, Laravel fakes and simple real objects over mocks.
* Keep test folders aligned with the tested domain.
* Use `Feature` for HTTP, Inertia, Artisan, authorization, validation, persistence and cross-layer flows; use `Unit` for isolated models, services, processing and domain rules.

## Database

* Use `RefreshDatabase` when reading or persisting data and prefer factories.
* Never depend on test order, pre-existing data or global seeders unless explicitly executed by the test.
* Tests use the local MySQL `akiba` database from `phpunit.xml` and may recreate its schema.
* Never modify production migration history to fix tests; preserve legacy MySQL-specific migrations.

## External Effects

* Use Laravel fakes for external effects and assert the expected result.
* Mock Socialite instead of calling real providers.
* Never send real Web Push notifications; test subscription persistence and selection instead.

## Coverage

* When relevant, private routes must cover unauthenticated, unauthorized and authorized users.
* Inertia pages must assert the component and essential props.
* Forms must cover valid input and important validation failures.
* State changes must assert the response and final database state.
* Models must cover relevant casts, relationships, accessors/mutators and scopes.
* Avoid unnecessary bootstrapping for directly testable rules.

## Execution

* Run tests with `./run.sh artisan test`; use suites, filters or specific files for diagnosis.
* Do not automatically run the frontend build after test changes.
* Do not start containers automatically; ask the user to run `./run.sh up` when required.
