<script>
    let className;
    export { className as class };
    export let size = "md";
    export let tone = "accent";
    export let label = "Carregando...";

    const sizes = {
        sm: "size-4 border-2",
        md: "size-6 border-[0.2rem]",
        lg: "size-8 border-4",
    };

    const tones = {
        accent: "border-orange-citric/25 border-t-orange-citric",
        primary: "border-blue-skywave/25 border-t-blue-skywave",
        light: "border-suspense-aurora/25 border-t-suspense-aurora",
    };

    $: classes = [
        "inline-block animate-spin rounded-full",
        sizes[size] ?? sizes.md,
        tones[tone] ?? tones.accent,
        className,
    ];
</script>

<span class={classes} role="status" aria-label={label}></span>
