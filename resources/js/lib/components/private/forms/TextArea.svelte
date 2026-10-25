<script>
    let className;
    export { className as class };
    export let id;
    export let value = null;
    export let error = null;
    export let variant = "default";
    export let describedBy = null;

    const variants = {
        default: "bg-white text-gray-900",
        offcanvas: "bg-white text-gray-900",
        modal: "bg-blue-marinho/55 text-suspense-aurora placeholder:text-blue-skywave/70",
        profile: "bg-suspense-aurora text-blue-marinho",
    };

    const borders = {
        default: "border border-gray-400",
        offcanvas: "border border-gray-400",
        modal: "border border-blue-skywave/35",
        profile: "border-0",
    };

    $: classes = [
        "w-full resize-none rounded-md px-4 py-2 font-noto-sans text-md outline-none",
        variants[variant] ?? variants.default,
        error ? "private-field-error" : borders[variant] ?? borders.default,
        className,
    ];
</script>

<textarea
    {...$$restProps}
    {id}
    bind:value
    aria-invalid={error ? "true" : undefined}
    aria-describedby={error ? `${id}-error` : describedBy}
    class={classes}
></textarea>

<style>
    :global(.private-field-error) {
        border: 2px solid var(--color-red-crimson) !important;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-red-crimson) 25%, transparent) !important;
    }
</style>
