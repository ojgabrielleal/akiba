<script>
    import { Link } from "@inertiajs/svelte";
    import { Section } from "@/lib/components/public";

    export let listenerGallery = [];
    export let styles = "container-page my-5";

    $: items = Array.isArray(listenerGallery) ? listenerGallery : listenerGallery?.data ?? [];
    const defaultPlaceholder = "/img/placeholders/placeholder.webp";
</script>

{#if items.length > 0}
    <Section title="Galeria do ouvinte" {styles}>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
            {#each items as item (item.uuid)}
                <article class="group overflow-hidden rounded-md transition duration-300 ease-out hover:-translate-y-0.5 hover:shadow-xl focus-within:-translate-y-0.5 motion-reduce:transform-none motion-reduce:transition-none">
                    <Link href={item.image} aria-label={`Abrir foto de ${item.listener_name ?? "ouvinte"}`} class="block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-amber">
                        <div class="aspect-[4/4.25]">
                            {#if item.image && item.image !== defaultPlaceholder}
                                <img
                                    src={item.image}
                                    alt={item.caption || item.listener_name || "Foto da galeria do ouvinte"}
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105 motion-reduce:transform-none motion-reduce:transition-none"
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
                    </Link>
                </article>
            {/each}
        </div>
    </Section>
{/if}
