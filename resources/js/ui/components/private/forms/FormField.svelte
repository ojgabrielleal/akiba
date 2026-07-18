<script>
    let forId;
    export { forId as for };
    export let label;
    export let help = null;
    export let error = null;
    export let className = "";
    export let labelVariant = "default";
    export let spacing = "md";

    const variants = {
        default: "text-md text-gray-700",
        editorial: "text-lg font-extrabold uppercase italic text-orange-amber",
        metadata: "font-extrabold uppercase italic text-blue-skywave",
        "metadata-indented": "ml-3 font-extrabold uppercase italic text-blue-skywave",
    };

    const spacings = {
        none: "mb-0",
        sm: "mb-2",
        compact: "mb-3",
        md: "mb-4",
        lg: "mb-8",
        section: "mb-6",
    };

    $: classes = [spacings[spacing] ?? spacings.md, className];
    $: labelClasses = [
        "mb-1 block font-noto-sans",
        variants[labelVariant] ?? variants.default,
    ];
</script>

<div class={classes}>
    <label for={forId} class={labelClasses}>
        {label}
    </label>
    <slot />
    {#if error}
        <div id={`${forId}-error`} class="mt-1 font-noto-sans text-sm text-red-crimson">
            {error}
        </div>
    {:else if help}
        <div class="mt-1 font-noto-sans text-sm text-gray-400">
            {help}
        </div>
    {/if}
</div>
