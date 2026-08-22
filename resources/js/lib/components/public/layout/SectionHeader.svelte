<script>
    let className;
    export { className as class };
    export let title;
    export let tone = "accent";
    export let size = "md";
    export let line = true;

    const tones = {
        accent: { text: "text-orange-citric", line: "bg-orange-citric" },
        primary: { text: "text-blue-skywave", line: "bg-blue-skywave" },
        light: { text: "text-suspense-aurora", line: "bg-suspense-aurora/40" },
    };

    const sizes = {
        sm: "text-xs",
        md: "text-sm",
        lg: "text-xl",
    };

    $: selectedTone = tones[tone] ?? tones.accent;
    $: classes = ["flex min-w-0 items-center gap-4", className];
</script>

<header class={classes}>
    <h2 class={[
        "public-section-heading-title shrink-0 font-noto-sans font-black uppercase italic",
        sizes[size] ?? sizes.md,
        selectedTone.text,
    ]}>
        {title}
    </h2>
    {#if line}
        <span class={["public-section-heading-line h-px min-w-4 flex-1", selectedTone.line]} aria-hidden="true"></span>
    {/if}
    {#if $$slots.actions}
        <div class="flex shrink-0 items-center gap-2">
            <slot name="actions" />
        </div>
    {/if}
</header>
