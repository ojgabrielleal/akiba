# Front-end Rules

Scope: `resources/js`.

## General

* Use Svelte + Inertia + Vite inside Laravel, Tailwind CSS v4 tokens from `css/app.css`, `font-noto-sans`, and `@/` for internal imports.
* Frontend is UI/client-only; server logic, database access, secrets, private SDKs and sensitive integrations belong to Laravel.
* Preserve `private`, `public`, `provisory` and `shared`; use `shared` only for genuinely cross-context code.

## Structure

* `pages`: Inertia pages and orchestration; `layouts`: persistent structures; `components`: small reusable UI; `widgets`: larger product/UI blocks; `stores`: shared client state; `utils`: pure/browser helpers; `constants`: static UI data; `css`: global styles, tokens and themes.
* Pages should stay thin; move large forms, tables and complex UI into widgets.
* Before creating anything, reuse existing components/widgets when possible.
* Keep extracted widget internals near their parent; when one generic widget becomes multiple files, group them in a same-named subfolder.
* Export reusable additions through the directory `index.js` when one exists; implementation-only internals need not be exported.
* Avoid thin wrappers and abstractions without meaningful responsibility.
* Reusable components should accept `class` and inputs/buttons should forward `$$restProps` when appropriate; keep visual variants in local maps with defaults.

## Inertia & State

* `Inertia::render()` names must map to files under `pages`.
* Pages are the primary `$page.props` boundary and should pass explicit, minimal props to widgets/components; avoid prop drilling and generic large objects.
* Small components must not read `$page.props`; layouts may for global/transversal data. Other access is limited to justified global components/helpers.
* Use stores only for shared client-side state, never as mirrors of backend props.
* Use `useForm`, pass Inertia errors to fields, use `forceFormData: true` for uploads, and normalize optional arrays/objects before building forms.

## Browser & Web Push

* Put reusable browser behavior such as Push, permissions, service workers, storage and global events in `utils`; components should call high-level helpers.
* Web Push uses `public/push-worker.js` and `lib/utils/push`.
* Read technical global props at their point of use and pass them to helpers rather than spreading browser API calls through components.
* Never expose private keys; only `VAPID_PUBLIC_KEY` may reach the browser.

## Svelte

* Order files as `<script>`, markup, then `<style>` when present.
* Script order: external imports, internal imports, props, constants, state, reactivity, pure helpers, handlers/actions; separate groups with blank lines.
* Use `const` unless reassignment/reactivity requires `let`.
* In Inertia pages, prefer one reactive block for `$page.props`.
* Avoid local state when a centralized utility already resolves the value.

## Styling

* Mobile-first; prefer responsive components over separate viewport versions unless interaction/markup genuinely differs.
* Use existing tokens and components; add new colors, gradients or filters to `css/app.css` before use.
* Public themes may change colors, gradients and filters only, never layout, spacing, typography, proportions, markup or elements.
* `orange-amber`: clickable/actions and their hover/focus/active states.
* `orange-citric` or `orange-morning`: non-clickable elements; displayed like metrics may use `orange-amber`.
* Clickable text cards/lists use accessible focus, `transition duration-300 ease-out`, slight `hover:-translate-y-0.5` and `motion-reduce`; image-only visuals use slight scale only.
* Keep layouts explicitly responsive and overflow-safe.

## Safety

* Do not modify outside `resources/js` unless explicitly required.
* Do not refactor unrelated code.
* Never put sensitive data, secrets, database access or server-only logic in the frontend.
