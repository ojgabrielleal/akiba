<script>
    let className;
    export { className as class };
    export let tone = "accent";
    export let spacing = "md";

    const tones = {
        accent: { line: "bg-orange-citric", text: "text-orange-citric" },
        primary: { line: "bg-blue-skywave", text: "text-blue-skywave" },
        light: { line: "bg-suspense-aurora/30", text: "text-suspense-aurora" },
    };

    const spacings = {
        none: "",
        sm: "my-3",
        md: "my-6",
        lg: "my-10",
    };

    $: selectedTone = tones[tone] ?? tones.accent;
    $: classes = [
        "flex w-full items-center gap-4",
        spacings[spacing] ?? spacings.md,
        className,
    ];
</script>

<div class={classes}>
    <span class={["public-section-heading-line h-px min-w-4 flex-1", selectedTone.line]} aria-hidden="true"></span>
    {#if $$slots.default}
        <div class={["public-section-heading-title shrink-0 font-noto-sans font-extrabold uppercase italic", selectedTone.text]}>
            <slot />
        </div>
        <span class={["public-section-heading-line h-px min-w-4 flex-1", selectedTone.line]} aria-hidden="true"></span>
    {/if}
</div>
