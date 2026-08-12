<script>
    let className;
    export { className as class };
    export let as = "div";
    export let variant = "ocean";
    export let padding = "md";
    export let radius = "md";

    const variants = {
        ocean: "bg-blue-ocean text-suspense-aurora",
        night: "bg-blue-night text-suspense-aurora",
        glass: "border border-suspense-aurora/10 bg-blue-ocean/25 text-suspense-aurora shadow-xl",
        light: "bg-suspense-aurora text-blue-night",
        accent: "bg-orange-amber text-blue-night",
        muted: "bg-neutral-gray text-blue-night",
    };

    const paddings = {
        none: "",
        sm: "p-3",
        md: "p-4",
        lg: "p-6",
    };

    const radii = {
        none: "rounded-none",
        sm: "rounded-sm",
        md: "rounded-md",
        lg: "rounded-2xl",
        full: "rounded-full",
    };

    $: classes = [
        variants[variant] ?? variants.ocean,
        paddings[padding] ?? paddings.md,
        radii[radius] ?? radii.md,
        className,
    ];
</script>

<svelte:element this={as} {...$$restProps} class={classes}>
    <slot />
</svelte:element>
