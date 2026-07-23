<script>
    let className;
    export { className as class };
    export let variant = "info";
    export let title = null;
    export let description = null;

    const variants = {
        info: "border-blue-skywave/40 bg-blue-skywave/10 text-blue-skywave",
        success: "border-green-mint/40 bg-green-mint/10 text-green-mint",
        warning: "border-orange-citric/40 bg-orange-citric/10 text-orange-citric",
        danger: "border-red-crimson/40 bg-red-crimson/10 text-red-crimson",
    };

    $: classes = [
        "rounded-md border px-4 py-3 font-noto-sans",
        variants[variant] ?? variants.info,
        className,
    ];
</script>

<div class={classes} role={variant === "danger" ? "alert" : "status"}>
    {#if title}
        <p class="font-extrabold uppercase italic">{title}</p>
    {/if}
    {#if description}
        <p class="mt-1 text-sm text-suspense-aurora/75">{description}</p>
    {/if}
    <slot />
</div>
