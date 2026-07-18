<script>
    let className;
    export { className as class };
    export let id;
    export let value = null;
    export let error = null;
    export let variant = "default";

    const variants = {
        default: "bg-white text-gray-900",
        offcanvas: "bg-white text-gray-900",
        profile: "bg-suspense-aurora text-blue-marinho",
    };

    const borders = {
        default: "border border-gray-400",
        offcanvas: "border border-gray-400",
        profile: "border-0",
    };

    $: classes = [
        "w-full resize-none rounded-md px-4 py-2 font-noto-sans text-md outline-none",
        variants[variant] ?? variants.default,
        error ? "border border-red-crimson" : borders[variant] ?? borders.default,
        className,
    ];
</script>

<textarea
    {...$$restProps}
    {id}
    bind:value
    aria-invalid={error ? "true" : undefined}
    aria-describedby={error ? `${id}-error` : undefined}
    class={classes}
></textarea>
