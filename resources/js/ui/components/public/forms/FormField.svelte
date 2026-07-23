<script>
    let className;
    let forId;
    export { className as class };
    export { forId as for };
    export let label;
    export let help = null;
    export let error = null;
    export let required = false;
    export let labelVariant = "default";
    export let spacing = "md";

    const variants = {
        default: "text-suspense-aurora/75",
        accent: "font-extrabold uppercase italic text-orange-citric",
        primary: "font-extrabold uppercase italic text-blue-skywave",
        compact: "text-[0.65rem] uppercase italic text-suspense-aurora/70",
        dark: "font-semibold text-blue-night/70",
    };

    const spacings = {
        none: "mb-0",
        sm: "mb-2",
        md: "mb-4",
        lg: "mb-8",
        section: "mb-6",
    };

    $: classes = [spacings[spacing] ?? spacings.md, className];
    $: labelClasses = [
        "mb-1 block font-noto-sans text-sm",
        variants[labelVariant] ?? variants.default,
    ];
</script>

<div class={classes}>
    <label for={forId} class={labelClasses}>
        {label}
        {#if required}
            <span class="text-orange-citric" aria-hidden="true">*</span>
        {/if}
    </label>
    <slot />
    {#if error}
        <div id={`${forId}-error`} class="mt-1 font-noto-sans text-sm text-red-crimson">
            {error}
        </div>
    {:else if help}
        <div class="mt-1 font-noto-sans text-sm text-suspense-aurora/45">
            {help}
        </div>
    {/if}
</div>
