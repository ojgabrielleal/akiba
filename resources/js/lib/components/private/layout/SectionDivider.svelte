<script>
    let className;
    export { className as class };
    export let tone = "sky";
    export let spacing = "md";
    
    const tones = {
        sky: {
            line: "bg-blue-skywave",
            text: "text-blue-skywave",
        },
        ocean: {
            line: "bg-blue-ocean",
            text: "text-blue-ocean",
        },
        accent: {
            line: "bg-orange-amber",
            text: "text-orange-amber",
        },
    };

    const spacings = {
        none: "",
        sm: "my-3",
        md: "mb-5 mt-8",
        lg: "my-8",
    };

    $: selectedTone = tones[tone] ?? tones.sky;
    $: classes = ["flex w-full items-center gap-4", spacings[spacing] ?? spacings.md, className];
</script>

<div class={classes}>
    <span class={["h-[0.1rem] min-w-4 flex-1 rounded-full", selectedTone.line]}></span>
    <div class={["shrink-0 font-noto-sans font-extrabold uppercase italic", selectedTone.text]}>
        <slot />
    </div>
    <span class={["h-[0.1rem] min-w-4 flex-1 rounded-full", selectedTone.line]}></span>
</div>
