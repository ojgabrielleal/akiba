#### Action Rules

1. Keep actions organized by scope folder, following the current `app/Actions` structure.

2. Name each action by operation and module, such as `StorePodcast` or `UpdateUser`.

3. Wrap writes and chained operations in `DB::transaction`.

4. Pass data and known models through method parameters; avoid re-querying models the caller already has.

5. Move extra work, lookups, or side effects into private methods inside the action.

6. Keep imports ordered as Laravel defaults/facades, exceptions, models, then services.
