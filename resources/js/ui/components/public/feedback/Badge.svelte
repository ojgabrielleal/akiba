<script>
    let className;
    export { className as class };
    export let variant = "accent";
    export let size = "sm";

    const variants = {
        accent: "bg-orange-citric text-blue-night",
        primary: "bg-blue-skywave text-suspense-aurora",
        ocean: "bg-blue-ocean text-suspense-aurora",
        light: "bg-suspense-aurora text-blue-night",
        success: "bg-green-mint text-blue-night",
        danger: "bg-red-crimson text-suspense-aurora",
        review: "bg-purple-mystic text-suspense-aurora",
        muted: "bg-neutral-gray text-blue-night",
    };

    const sizes = {
        xs: "px-2 py-0.5 text-[0.6rem]",
        sm: "px-3 py-1 text-xs",
        md: "px-4 py-1.5 text-sm",
        lg: "px-5 py-2 text-base",
    };

    $: classes = [
        "inline-flex items-center justify-center gap-1 truncate rounded-full font-noto-sans font-extrabold uppercase italic leading-none",
        variants[variant] ?? variants.accent,
        sizes[size] ?? sizes.sm,
        className,
    ];
</script>

<span {...$$restProps} class={classes}>
    <slot />
</span>
