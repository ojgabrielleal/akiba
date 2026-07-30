<script>
    let className;
    export { className as class };
    export let as = "ul";
    export let preset = "articles";

    const presets = {
        articles: "grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 lg:gap-x-10",
        cards: "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4",
        media: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5",
        reviews: "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3",
        team: "grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4",
        wide: "grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-5",
        split: "grid-cols-1 lg:grid-cols-2 gap-5",
    };

    $: classes = ["grid", presets[preset] ?? presets.articles, className];
</script>

<svelte:element this={as} {...$$restProps} class={classes}>
    <slot />
</svelte:element>
