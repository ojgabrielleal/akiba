# Integration Rules

Scope: `app/Integrations`.

* Integrations encapsulate external services, APIs, webhooks, streams and providers, including URLs, headers, payloads, timeouts and basic failure handling.
* Keep files directly in `app/Integrations` unless a real subscope exists; do not use `External` or `Process` folders.
* Business rules belong to `app/Services`; reusable internal processing belongs to `app/Processing`.
* Read credentials and URLs through `config/services.php`, never directly from `env()` outside config files.
* Do not mix domain persistence with external calls.
* Use Laravel Socialite for public OAuth; do not recreate provider redirect, token exchange or user-fetch integrations.
* Push notifications use native Web Push through `PushNotificationService`; never reintroduce OneSignal.
* Return simple data, DTOs or arrays as appropriate for consuming services.
* Log external failures with useful context without exposing secrets.
* Isolate external calls in tests using appropriate Laravel/contract fakes or mocks.
