<script>
    let className;
    export { className as class };
    export let title;
    export let compact = false;
    export let listLabel = null;
</script>

<div class={className}>
    <header
        class={[
            "relative isolate overflow-hidden bg-cover bg-right bg-no-repeat lg:bg-contain",
            compact ? "py-3" : "py-5",
        ]}
        style="background-image: url('/img/textures/screentone.webp'), var(--gradient-blue-ocean-cerulean);"
    >
        <div class="container-page relative">
            <h1 class="break-words text-center font-noto-sans text-4xl font-black italic uppercase leading-none text-orange-citric sm:text-5xl lg:text-6xl">
                {title}
            </h1>
        </div>
    </header>

    {#if $$slots.default}
        <nav class="bg-blue-night" aria-label={listLabel ?? title}>
            <ul class="editorial-title-list container-page flex flex-wrap items-center justify-center gap-y-3 py-6 sm:py-8">
                <slot />
            </ul>
        </nav>
    {/if}
</div>

<style>
    .editorial-title-list :global(a),
    .editorial-title-list :global(button) {
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 639px) {
        .editorial-title-list :global(a),
        .editorial-title-list :global(button) {
            font-size: 0.875rem;
        }

        .editorial-title-list :global(img) {
            height: 1.25rem;
            width: 1.25rem;
        }
    }
</style>
