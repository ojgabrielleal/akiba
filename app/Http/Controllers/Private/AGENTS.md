#### Controller Rules

1. Keep page renderers in `/Pages`, non-CRUD handlers in `/Invokes`, and module CRUD controllers in the folder root.

2. `store`, `update`, and `delete` work must go through `app/Actions`, injected as method parameters.

3. Validated input must use `app/Http/Requests`, injected as method parameters.

4. Keep method parameters ordered as request, action, then route-bound model.

5. Page controllers must render through a `render` method and gather props through private methods such as `indexPosts`; page queries should use constructor-injected filters.

6. Keep `render` as the first behavior method. When a page needs a shared model/query result, fetch it with a private `get*` method placed immediately after `render`, then pass that result into the prop methods that need it.

7. Use `index*` private methods to build page props/resources. Do not use `show*` as a prop helper; reserve `show` methods for controller actions that return `InertiaRender`.

8. Keep simple relation arrays or strings directly in the corresponding `with`/`load`. Create private `*Relations` methods only when the relation set has query callbacks, chained relation logic, or is shared by multiple queries in the same scope.

9. Import dependencies with `use` statements before the class, ordered as Laravel defaults, exceptions, models, requests, resources, actions, then services.

10. Private controllers must authorize each method; when a FormRequest validates the method, put authorization in the request.

11. `show` methods must return `InertiaRender` with the corresponding prop and any page props the UI still needs.
