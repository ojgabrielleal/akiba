<script>
    import { MinimalEmptyState, Section } from "@/lib/components/public";
    import { publicAnimations } from "@/lib/constants/public/animation";

    export let listenerGallery = [];
    export let styles = "container-page my-5";
    export let emptyTitle = "Nenhuma mídia enviada";
    export let emptyMessage = "As fotos da comunidade aparecem aqui quando forem publicadas.";

    let selectedItem = null;

    $: items = Array.isArray(listenerGallery) ? listenerGallery : listenerGallery?.data ?? [];
    const defaultPlaceholder = "/img/placeholders/placeholder.webp";

    const itemAlt = (item) => item.caption || item.listener_name || "Foto da galeria do ouvinte";
    const hasImage = (item) => item.image && item.image !== defaultPlaceholder;

    const openLightbox = (item) => {
        if (!hasImage(item)) return;

        selectedItem = item;
    };

    const closeLightbox = () => {
        selectedItem = null;
    };

    const handleKeydown = (event) => {
        if (selectedItem && event.key === "Escape") {
            closeLightbox();
        }
    };
</script>

<svelte:window on:keydown={handleKeydown} />

{#if selectedItem}
    <div class="fixed inset-0 z-[220] flex items-center justify-center bg-blue-night/72 p-3 backdrop-blur-xs sm:p-5 lg:p-8">
        <button
            type="button"
            class="absolute inset-0 cursor-zoom-out focus-visible:outline-none"
            aria-label="Fechar imagem"
            on:click={closeLightbox}
        ></button>
        <button
            type="button"
            class={["absolute right-4 top-4 z-20 flex size-11 cursor-pointer items-center justify-center rounded-full bg-orange-amber text-blue-night shadow-lg shadow-blue-night/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-suspense-aurora sm:right-6 sm:top-6", publicAnimations.iconButtonInteractive]}
            aria-label="Fechar imagem"
            on:click={closeLightbox}
        >
            <img src="/svg/close.svg" alt="" aria-hidden="true" class="size-4 brightness-0" />
        </button>
        <div
            class="relative flex max-h-[94dvh] w-full max-w-7xl cursor-default items-center justify-center"
            role="dialog"
            aria-modal="true"
            aria-label={`Foto da galeria enviada por ${selectedItem.listener_name || "Ouvinte Akiba"}`}
        >
            <figure class="relative max-h-[94dvh] max-w-full overflow-hidden rounded-md">
                <img
                    src={selectedItem.image}
                    alt={itemAlt(selectedItem)}
                    class="max-h-[94dvh] max-w-full object-contain"
                />

                <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-blue-night via-blue-night/90 to-blue-night/0 px-4 pb-4 pt-16 font-noto-sans text-suspense-aurora sm:px-6 sm:pb-6">
                    <div class="mx-auto max-w-3xl">
                        <p class="text-xs font-black uppercase italic tracking-[0.18em] text-white">
                            Enviado por:
                        </p>
                        <h3 class="break-words text-xl font-black uppercase italic leading-tight text-white sm:text-2xl">
                            {selectedItem.listener_name || "Ouvinte Akiba"}
                        </h3>

                        {#if selectedItem.caption}
                            <p class="mt-1 max-w-2xl break-words text-xs font-normal leading-5 text-white sm:text-sm">
                                {selectedItem.caption}
                            </p>
                        {/if}
                    </div>
                </figcaption>
            </figure>
        </div>
    </div>
{/if}

<Section title="Galeria do ouvinte" {styles}>
    {#if items.length > 0}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
            {#each items as item (item.uuid)}
                <article class="overflow-hidden rounded-md">
                    <button
                        type="button"
                        class={["group block w-full cursor-zoom-in overflow-hidden rounded-md text-left disabled:cursor-not-allowed disabled:opacity-70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber", publicAnimations.cardInteractive]}
                        aria-label={`Ver foto enviada por ${item.listener_name || "Ouvinte Akiba"} em tela cheia`}
                        disabled={!hasImage(item)}
                        on:click={() => openLightbox(item)}
                    >
                        <div class="aspect-[4/4.25] overflow-hidden">
                            {#if hasImage(item)}
                                <img
                                    src={item.image}
                                    alt={itemAlt(item)}
                                    class={["h-full w-full object-cover", publicAnimations.imageZoom]}
                                    loading="lazy"
                                />
                            {/if}
                        </div>
                        <div class="flex min-h-12 items-center bg-orange-amber px-3 py-1.5 text-blue-night transition duration-300 ease-out group-hover:brightness-110 group-focus-visible:brightness-110 motion-reduce:transition-none">
                            <h3 class="line-clamp-1 font-noto-sans text-base font-black uppercase italic">
                                Por: {item.listener_name || "Ouvinte Akiba"}
                            </h3>
                        </div>
                    </button>
                </article>
            {/each}
        </div>
    {:else}
        <MinimalEmptyState title={emptyTitle} message={emptyMessage} />
    {/if}
</Section>
