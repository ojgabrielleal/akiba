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
        "mt-0.5 size-4 shrink-0 cursor-pointer accent-orange-citric focus:ring-orange-citric",
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
    <label for={id} class="cursor-pointer font-noto-sans text-sm text-suspense-aurora">
        <span class="block font-semibold">{label}</span>
        {#if description}
            <span class="line-clamp-2 block text-xs text-suspense-aurora/45">
                {description}
            </span>
        {/if}
    </label>
</div>
