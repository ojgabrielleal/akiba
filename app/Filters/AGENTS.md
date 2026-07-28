#### Filter Rules

1. Keep filters in the root of `app/Filters`.

2. Name filters after the model plus `Filter`, such as `UserFilter` or `CalendarFilter`.

3. Filter entry points must receive `array $filters = []`.

4. Build queries with Eloquent `when`, apply default ordering, and return pagination only when requested; otherwise return the collection.

5. Keep filters focused on query composition for their corresponding model.
