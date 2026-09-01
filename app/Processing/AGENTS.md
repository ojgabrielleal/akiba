# Processing Rules

Scope: `app/Processing`.

* Processing handles reusable internal file manipulation, data transformation, collection and routines that are neither business rules nor external integrations.
* Keep files directly in `app/Processing`, using the `Process` suffix; avoid subfolders unless a clear subscope exists.
* Business rules belong to `app/Services`; external APIs and integrations belong to `app/Integrations`.
* Processes may be injected into services and must receive collaborators explicitly through the constructor.
* Keep properties/constructor at the top and use processing-oriented method names such as `store`, `delete` and `collect`.
* Keep processes small, predictable and reusable.
* Test them in `tests/Unit/Processing`, or `tests/Unit/Services` while the legacy structure still exists.
