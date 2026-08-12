<script>
    let className = "";
    export { className as class };
    export let variant = "light";
    export let size = "sm";

    const variants = {
        light: "bg-suspense-aurora text-blue-marinho",
        primary: "bg-blue-skywave text-suspense-aurora",
        dark: "bg-blue-marinho text-suspense-aurora",
        accent: "bg-orange-amber text-blue-marinho",
        success: "bg-green-forest text-suspense-aurora",
        danger: "bg-red-crimson text-suspense-aurora",
        review: "bg-purple-mystic text-suspense-aurora",
    };

    const sizes = {
        sm: "px-4 py-1 text-xs",
        md: "px-4 py-1.5 text-sm",
        lg: "px-5 py-2 text-md",
    };

    $: classes = [
        "inline-flex items-center justify-center gap-1 truncate rounded-full font-noto-sans font-extrabold uppercase italic leading-none",
        variants[variant] ?? variants.light,
        sizes[size] ?? sizes.sm,
        className,
    ];
</script>

<span {...$$restProps} class={classes}>
    <slot />
</span>
