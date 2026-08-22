<script>
    import { MinimalEmptyState, Section } from "@/lib/components/public";

    export let listenerGallery = [];
    export let styles = "container-page my-5";
    export let emptyTitle = "Nenhuma mídia enviada";
    export let emptyMessage = "As fotos da comunidade aparecem aqui quando forem publicadas.";

    $: items = Array.isArray(listenerGallery) ? listenerGallery : listenerGallery?.data ?? [];
    const defaultPlaceholder = "/img/placeholders/placeholder.webp";
</script>

<Section title="Galeria do ouvinte" {styles}>
    {#if items.length > 0}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
            {#each items as item (item.uuid)}
                <article class="overflow-hidden rounded-md">
                    <div class="aspect-[4/4.25] overflow-hidden">
                        {#if item.image && item.image !== defaultPlaceholder}
                            <img
                                src={item.image}
                                alt={item.caption || item.listener_name || "Foto da galeria do ouvinte"}
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                        {/if}
                    </div>
                    <div class="min-h-14 bg-orange-amber px-3 py-2 text-blue-night">
                        <h3 class="line-clamp-1 font-noto-sans text-base font-black uppercase italic">
                            {item.listener_name || "Ouvinte Akiba"}
                        </h3>
                        {#if item.caption}
                            <p class="line-clamp-1 font-noto-sans text-sm font-bold">
                                {item.caption}
                            </p>
                        {/if}
                    </div>
                </article>
            {/each}
        </div>
    {:else}
        <MinimalEmptyState title={emptyTitle} message={emptyMessage} />
    {/if}
</Section>
