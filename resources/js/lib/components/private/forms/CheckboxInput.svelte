<script>
    let className;
    export { className as class };
    export let variant = "default";
    export let id;
    export let label;
    export let description = null;
    export let icon = null;
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

{#if variant === "card"}
    {@const selected = group !== undefined ? isGroupChecked : checked}
    <label class={["group/role relative grid min-w-0 cursor-pointer grid-cols-[1fr_auto] gap-2 rounded-lg border p-3 font-noto-sans transition duration-300 ease-out focus-within:ring-2 focus-within:ring-blue-marinho/20 motion-reduce:transition-none has-[:disabled]:cursor-default has-[:disabled]:opacity-50", selected ? "border-blue-marinho/10 bg-orange-citric/10" : "border-blue-marinho/10 bg-neutral-white", className]}>
        {#if group !== undefined}
            <input {...$$restProps} {id} {value} type="checkbox" aria-describedby={description ? `${id}-description` : undefined} class="sr-only" checked={isGroupChecked} aria-invalid={error ? "true" : undefined} on:change={updateGroup} />
        {:else}
            <input {...$$restProps} {id} type="checkbox" aria-describedby={description ? `${id}-description` : undefined} class="sr-only" bind:checked aria-invalid={error ? "true" : undefined} />
        {/if}
        <span class={["flex size-9 items-center justify-center rounded-lg", selected ? "bg-orange-citric/20 text-blue-marinho" : "bg-blue-marinho/5 text-blue-marinho/50"]} aria-hidden="true">
            {#if icon}
                <img src={icon} alt="" class="size-4 object-contain grayscale brightness-0 opacity-60" />
            {:else}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="size-4"><circle cx="12" cy="8" r="3" /><path d="M5 21v-2a7 7 0 0 1 14 0v2" /></svg>
            {/if}
        </span>
        <span class={["flex size-4 items-center justify-center self-center rounded border", selected ? "border-orange-citric bg-orange-citric text-blue-marinho" : "border-blue-marinho/20 bg-neutral-white"]} aria-hidden="true">
            {#if selected}
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" class="size-3"><path d="m3 8 3 3 7-7" /></svg>
            {/if}
        </span>
        <span class="col-span-2 min-w-0">
            <span class="block break-words text-xs font-bold leading-snug text-blue-marinho">{label}</span>
            {#if description}
                <span id={`${id}-description`} role="tooltip" class="invisible absolute inset-x-0 bottom-full z-50 mb-2 rounded-md bg-blue-marinho px-3 py-2 text-xs font-normal leading-relaxed text-suspense-aurora shadow-lg group-hover/role:visible group-focus-within/role:visible">{description}<span aria-hidden="true" class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-blue-marinho"></span></span>
            {/if}
        </span>
    </label>
{:else}
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

{/if}
