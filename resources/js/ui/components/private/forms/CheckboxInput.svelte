<script>
    export let id;
    export let label;
    export let description = null;
    export let value = null;
    export let checked = false;
    export let group = undefined;
    export let className = "";

    $: classes = [
        "size-4 shrink-0 cursor-pointer border-gray-300 text-blue-skywave focus:ring-blue-skywave",
        className,
    ];

    const labelClasses = "cursor-pointer font-noto-sans text-md text-gray-700";
</script>

<div class="flex items-start gap-2">
    {#if group !== undefined}
        <input
            {...$$restProps}
            {id}
            {value}
            class={classes}
            type="checkbox"
            bind:group
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
