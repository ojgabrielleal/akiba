<script>
    let className;
    export { className as class };
    export let id;
    export let label;
    export let description = null;
    export let value = null;
    export let checked = false;
    export let group = undefined;
    export let error = null;
    export let labelClass = "cursor-pointer font-noto-sans text-md text-gray-700";
    export let descriptionClass = "line-clamp-2 block text-xs text-gray-400";

    $: classes = [
        "size-4 shrink-0 cursor-pointer text-blue-skywave focus:ring-blue-skywave",
        error ? "border border-red-crimson" : "border-gray-300",
        className,
    ];

    $: groupValues = Array.isArray(group) ? group : [];
    $: isGroupChecked = groupValues.includes(value);

    const updateGroup = (event) => {
        group = event.currentTarget.checked
            ? [...new Set([...groupValues, value])]
            : groupValues.filter((item) => item !== value);
    };
</script>

<div class="flex items-start gap-2">
    {#if group !== undefined}
        <input
            {...$$restProps}
            {id}
            {value}
            class={classes}
            type="checkbox"
            checked={isGroupChecked}
            aria-invalid={error ? "true" : undefined}
            aria-describedby={error ? `${id}-error` : undefined}
            on:change={updateGroup}
        />
    {:else}
        <input
            {...$$restProps}
            {id}
            class={classes}
            type="checkbox"
            aria-invalid={error ? "true" : undefined}
            aria-describedby={error ? `${id}-error` : undefined}
            bind:checked
        />
    {/if}
    <label for={id} class={labelClass}>
        <span class="block text-sm font-semibold">
            {label}
        </span>
        {#if description}
            <span class={descriptionClass}>
                {description}
            </span>
        {/if}
    </label>
</div>
