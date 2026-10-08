<script>
    let className;
    export { className as class };
    export let title;
    export let compact = false;
    export let listLabel = null;
    export let phrase = null;
    export let padding = "py-4 sm:py-5";
    export let phrasePadding = "py-5";
    export let phraseMinHeight = "min-h-20 sm:min-h-24";
    export let spacer = false;
    export let topSpacing = "pt-5";
    export let headingClass = "";
</script>

<div class={["public-editorial-title bg-blue-night", topSpacing, className]}>
    <header
        class={[
            "public-editorial-title-hero relative isolate overflow-hidden bg-cover bg-right bg-no-repeat lg:bg-contain",
            compact ? "flex min-h-14 items-center py-3" : "flex h-[90px] items-center",
        ]}
        style="--public-editorial-title-texture: url('/img/textures/tecnologia_texture.svg'); background-image: var(--gradient-blue-ocean-cerulean);"
    >
        <span class={["editorial-title-texture pointer-events-none absolute inset-0 z-0 bg-no-repeat [background-size:cover] lg:[background-size:contain]", compact ? "editorial-title-texture-compact" : ""]} style="background-image: var(--public-editorial-title-texture);" aria-hidden="true"></span>
        <div class="container-page relative z-10">
            <h1 class={["public-editorial-title-heading break-words text-center font-noto-sans text-4xl font-black italic uppercase leading-none text-[#ffffff] sm:text-5xl lg:text-5xl", headingClass]}>
                {title}
            </h1>
        </div>
    </header>

    {#if phrase}
        <div class="public-editorial-title-phrase">
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
    .editorial-title-texture {
        background-position: right center;
        filter: var(--public-title-texture-filter) brightness(0.3);
    }

    @media (min-width: 1024px) {
        .editorial-title-texture {
            filter: var(--public-title-texture-filter);
        }
    }

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
