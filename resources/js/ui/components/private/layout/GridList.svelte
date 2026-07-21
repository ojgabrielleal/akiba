<script>
    let className;
    export { className as class };
    export let as = "ul";
    export let preset = "content";

    const presets = {
        content: "grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5",
        media: "grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3",
        wide: "grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-5",
        split: "grid-cols-1 lg:grid-cols-2 gap-5",
        calendar: "grid-cols-7 gap-3",
        members: "grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-x-5 gap-y-16",
    };

    $: classes = ["grid", presets[preset] ?? presets.content, className];
</script>

<svelte:element this={as} {...$$restProps} class={classes}>
    <slot />
</svelte:element>
