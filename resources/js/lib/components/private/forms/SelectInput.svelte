<script>
    let className;
    export { className as class };
    export let id;
    export let value = null;
    export let error = null;
    export let variant = "default";

    const variants = {
        default: "h-10 rounded-md bg-white text-gray-900",
        offcanvas: "h-10 rounded-md bg-white text-gray-900",
        pill: "h-12 rounded-full bg-suspense-aurora text-blue-marinho",
        profile: "h-12 rounded-md bg-suspense-aurora text-blue-marinho",
    };

    const borders = {
        default: "border border-gray-400",
        offcanvas: "border border-gray-400",
        pill: "border-0",
        profile: "border-0",
    };

    $: classes = [
        "w-full appearance-none pl-4 pr-10 font-noto-sans text-md outline-none",
        variants[variant] ?? variants.default,
        error ? "private-field-error" : borders[variant] ?? borders.default,
        className,
    ];
</script>

<div class="relative">
    <select
        {...$$restProps}
        {id}
        aria-invalid={error ? "true" : undefined}
        aria-describedby={error ? `${id}-error` : undefined}
        class={classes}
        bind:value
    >
        <slot />
    </select>
    <img
        src="/svg/chevron-down.svg"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute top-1/2 right-3 size-5 -translate-y-1/2 opacity-80 filter-blue-marinho"
    />
</div>

<style>
    :global(.private-field-error) {
        border: 2px solid var(--color-red-crimson) !important;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-red-crimson) 25%, transparent) !important;
    }
</style>
