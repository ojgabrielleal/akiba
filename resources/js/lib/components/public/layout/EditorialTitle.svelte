<script>
    let className;
    export { className as class };
    export let title;
    export let compact = false;
    export let listLabel = null;
    export let phrase = null;
    export let padding = "py-6 sm:py-8";
    export let phrasePadding = "py-5";
    export let phraseMinHeight = "min-h-20 sm:min-h-24";
    export let spacer = false;
</script>

<div class={["public-editorial-title", className]}>
    <header
        class={[
            "public-editorial-title-hero relative isolate overflow-hidden bg-cover bg-right bg-no-repeat lg:bg-contain",
            compact ? "py-3" : "py-5",
        ]}
        style="--public-editorial-title-texture: url('/img/textures/screentone.webp'); background-image: var(--public-editorial-title-texture), var(--public-editorial-title-gradient, var(--gradient-blue-ocean-cerulean));"
    >
        <div class="container-page relative">
            <h1 class="public-editorial-title-heading break-words text-center font-noto-sans text-4xl font-black italic uppercase leading-none text-orange-morning sm:text-5xl lg:text-6xl">
                {title}
            </h1>
        </div>
    </header>

    {#if phrase}
        <div class="public-editorial-title-phrase bg-blue-night">
            <p class={["container-page flex items-center justify-center text-center font-noto-sans text-sm font-extrabold italic uppercase text-neutral-gray", phraseMinHeight, phrasePadding]}>
                {phrase}
            </p>
        </div>
    {:else if $$slots.default}
        <nav class="bg-blue-night" aria-label={listLabel ?? title}>
            <ul class={["editorial-title-list container-page flex flex-wrap items-center justify-center gap-y-3", padding]}>
                <slot />
            </ul>
        </nav>
    {:else if spacer && padding}
        <div class={["bg-blue-night", padding]}></div>
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
