<script>
    export let type = "button";
    export let variant = "primary";
    export let size = "md";
    export let shape = "rounded";
    export let loading = false;
    export let disabled = false;
    export let className = "";


    const variants = {
        primary: "bg-blue-skywave text-suspense-aurora hover:brightness-95",
        secondary: "bg-blue-marinho text-suspense-aurora hover:brightness-110",
        info: "bg-blue-ocean text-suspense-aurora hover:brightness-110",
        accent: "bg-orange-citric text-blue-marinho hover:brightness-95",
        success: "bg-green-forest text-blue-marinho hover:brightness-95",
        review: "bg-purple-mystic text-suspense-aurora hover:brightness-110",
        danger: "bg-red-crimson text-suspense-aurora hover:brightness-110",
        outline: "border-2 border-blue-skywave text-blue-skywave hover:bg-blue-skywave/10",
    };

    const sizes = {
        sm: "px-3 py-1 text-sm",
        md: "px-4 py-2 text-md",
        lg: "px-8 py-2 text-md",
    };

    const shapes = {
        rounded: "rounded-md",
        pill: "rounded-full",
    };

    $: classes = [
        "inline-flex cursor-pointer items-center justify-center gap-2 font-noto-sans font-extrabold uppercase italic transition disabled:cursor-not-allowed disabled:opacity-50",
        variants[variant] ?? variants.primary,
        sizes[size] ?? sizes.md,
        shapes[shape] ?? shapes.rounded,
        className,
    ];
</script>

<button
    {...$$restProps}
    {type}
    class={classes}
    disabled={disabled || loading}
    aria-busy={loading}
    on:click
>
    {#if loading}
        <span
            class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"
            aria-hidden="true"
        ></span>
    {/if}
    <slot />
</button>
