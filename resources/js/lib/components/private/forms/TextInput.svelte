<script>
    let className;
    export { className as class };
    export let id;
    export let type = "text";
    export let value = null;
    export let error = null;
    export let variant = "default";

    const variants = {
        default: "h-10 rounded-md bg-white text-gray-900",
        offcanvas: "h-10 rounded-md bg-white text-gray-900",
        editorial: "h-12 rounded-md bg-blue-ocean text-suspense-aurora",
        profile: "h-12 rounded-md bg-suspense-aurora text-blue-marinho",
        pillLeft: "h-12 rounded-l-full bg-suspense-aurora text-blue-marinho",
        pillRight: "h-12 rounded-r-full bg-suspense-aurora text-blue-marinho",
    };

    const borders = {
        default: "border border-gray-400",
        offcanvas: "border border-gray-400",
        editorial: "border border-blue-skywave",
        profile: "border-0",
        pillLeft: "border-0 border-r border-blue-marinho",
        pillRight: "border-0",
    };

    $: classes = [
        "w-full pl-4 font-noto-sans text-md outline-none",
        variants[variant] ?? variants.default,
        error ? "private-field-error" : borders[variant] ?? borders.default,
        className,
    ];
</script>

<input
    {...$$restProps}
    {id}
    {type}
    aria-invalid={error ? "true" : undefined}
    aria-describedby={error ? `${id}-error` : undefined}
    class={classes}
    bind:value
/>

<style>
    :global(.private-field-error) {
        border: 2px solid var(--color-red-crimson) !important;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-red-crimson) 25%, transparent) !important;
    }
</style>
