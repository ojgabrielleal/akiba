<script>
    import { Link } from "@inertiajs/svelte";
    import { publicAnimations } from "@/lib/constants";
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
                <article class={["group overflow-hidden rounded-md", publicAnimations.cardInteractive]}>
                    <Link href={item.image} aria-label={`Abrir foto de ${item.listener_name ?? "ouvinte"}`} class="block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-citric">
                        <div class="aspect-[4/4.25] overflow-hidden">
                            {#if item.image && item.image !== defaultPlaceholder}
                                <img
                                    src={item.image}
                                    alt={item.caption || item.listener_name || "Foto da galeria do ouvinte"}
                                    class={["h-full w-full object-cover", publicAnimations.imageZoom]}
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
