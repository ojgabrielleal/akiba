<script>
    import { Carousel, EmptyState, Section } from "@/lib/components/private";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let title;
    export let audience = null;

    function listenersLabel(listeners) {
        if (listeners === 0) return "0 ouvintes";
        return `${Number(listeners).toLocaleString("pt-BR")} ouvintes`;
    }
</script>

<Section {title}>
    {#if audience?.data?.length}
        <Carousel class="audience-carousel" label={title} scrollAmount={0.9}>
            {#each audience.data as item (item.uuid)}
                <article class="flex h-48 w-36 shrink-0 flex-col overflow-hidden rounded-md bg-blue-ocean font-noto-sans sm:w-40" aria-label={`${item.name}: ${item.status === "online" ? listenersLabel(item.listeners) : "fora do ar"}`}>
                    <header class={["flex h-6 shrink-0 items-center justify-center px-2 text-center text-[0.6rem] font-extrabold uppercase text-suspense-aurora",
                        item.status === "online" ? "bg-blue-cerulean" : "bg-red-crimson",
                    ]}>
                        <span class="truncate">{item.name}</span>
                    </header>
                    <a
                        href={item.website}
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label={`Visitar o site da ${item.name}`}
                        title={`Visitar o site da ${item.name}`}
                        class="flex min-h-0 flex-1 items-center justify-center p-5 transition hover:bg-blue-skywave/10 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-orange-citric"
                    >
                        <img
                            src={resolvePlaceholderImage(item.logo, "placeholder")}
                            alt={`Logo da ${item.name}`}
                            loading="lazy"
                            class="max-h-20 max-w-full object-contain"
                        />
                    </a>
                    <footer class={["flex h-10 shrink-0 items-center justify-center px-2 text-center text-sm font-black uppercase italic text-suspense-aurora",
                        item.status === "online" ? "bg-blue-cerulean" : "bg-red-crimson",
                    ]}>
                        {item.status === "online" ? listenersLabel(item.listeners) : "Fora do ar"}
                    </footer>
                </article>
            {/each}
        </Carousel>
    {:else}
        <EmptyState
            title="Nenhuma audiência disponível"
            description="As medições aparecerão após a primeira coleta."
        />
    {/if}
</Section>

<style>
    :global(.audience-carousel .carousel-scroll) {
        gap: 0.65rem;
    }
</style>
