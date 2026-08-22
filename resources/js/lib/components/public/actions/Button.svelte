<script>
    import { publicAnimations } from "@/lib/constants";

    let className;
    export { className as class };
    export let type = "button";
    export let variant = "primary";
    export let size = "md";
    export let shape = "rounded";
    export let loading = false;
    export let disabled = false;

    const variants = {
        primary: "bg-orange-amber text-blue-night hover:brightness-105",
        secondary: "bg-blue-skywave text-suspense-aurora hover:brightness-110",
        dark: "bg-blue-ocean text-suspense-aurora hover:bg-blue-cerulean",
        light: "bg-suspense-aurora text-blue-night hover:brightness-95",
        success: "bg-green-mint text-blue-night hover:brightness-105",
        danger: "bg-red-crimson text-suspense-aurora hover:brightness-110",
        outline: "border-2 border-orange-amber text-orange-amber hover:bg-orange-amber/10",
        ghost: "bg-transparent text-suspense-aurora hover:bg-suspense-aurora/10",
    };

    const sizes = {
        sm: "min-h-8 px-3 py-1 text-xs",
        md: "min-h-10 px-5 py-2 text-sm",
        lg: "min-h-12 px-8 py-2.5 text-base",
    };

    const shapes = {
        rounded: "rounded-md",
        pill: "rounded-full",
        square: "rounded-none",
    };

    $: classes = [
        "inline-flex cursor-pointer items-center justify-center gap-2 font-noto-sans font-extrabold uppercase italic disabled:cursor-not-allowed disabled:opacity-50",
        publicAnimations.buttonInteractive,
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
            class="size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent"
            aria-hidden="true"
        ></span>
    {/if}
    <slot />
</button>
