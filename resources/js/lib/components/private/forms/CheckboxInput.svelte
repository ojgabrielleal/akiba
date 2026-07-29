<script>
    let className;
    export { className as class };
    export let id;
    export let label;
    export let description = null;
    export let value = null;
    export let checked = false;
    export let group = undefined;

    $: classes = [
        "size-4 shrink-0 cursor-pointer border-gray-300 text-blue-skywave focus:ring-blue-skywave",
        className,
    ];

    const labelClasses = "cursor-pointer font-noto-sans text-md text-gray-700";

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
            on:change={updateGroup}
        />
    {:else}
        <input
            {...$$restProps}
            {id}
            class={classes}
            type="checkbox"
            bind:checked
        />
    {/if}
    <label for={id} class={labelClasses}>
        <span class="block text-sm font-semibold">
            {label}
        </span>
        {#if description}
            <span class="line-clamp-2 block text-xs text-gray-400">
                {description}
            </span>
        {/if}
    </label>
</div>
