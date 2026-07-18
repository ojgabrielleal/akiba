<script>
    export let id;
    export let value = null;
    export let error = null;
    export let className = "";
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
        "w-full pl-4 font-noto-sans text-md outline-none",
        variants[variant] ?? variants.default,
        error ? "border border-red-crimson" : borders[variant] ?? borders.default,
        className,
    ];
</script>

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
