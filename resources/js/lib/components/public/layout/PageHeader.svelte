<script>
    let className;
    export { className as class };
    export let title;
    export let subtitle = null;
    export let align = "center";
    export let size = "lg";
    export let tone = "accent";

    const alignments = {
        left: "text-left",
        center: "text-center",
        right: "text-right",
    };

    const sizes = {
        md: "text-2xl sm:text-3xl",
        lg: "text-3xl sm:text-4xl",
        xl: "text-4xl sm:text-5xl",
    };

    const tones = {
        accent: "text-orange-morning",
        primary: "text-blue-skywave",
        light: "text-suspense-aurora",
    };

    $: classes = [
        "w-full overflow-hidden bg-gradient-blue-ocean-skywave px-4 py-3 sm:py-4",
        alignments[align] ?? alignments.center,
        className,
    ];
</script>

<header class={classes}>
    <h1 class={[
        "font-noto-sans font-black uppercase italic leading-none",
        sizes[size] ?? sizes.lg,
        tones[tone] ?? tones.accent,
    ]}>
        {title}
    </h1>
    {#if subtitle}
        <p class="mt-2 font-noto-sans text-xs uppercase italic tracking-wide text-suspense-aurora/65 sm:text-sm">
            {subtitle}
        </p>
    {/if}
</header>
