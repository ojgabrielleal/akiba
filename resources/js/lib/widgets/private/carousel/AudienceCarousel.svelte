<script>
    import { router } from "@inertiajs/svelte";

    import { Carousel, EmptyState, IconButton, Offcanvas, Section } from "@/lib/components/private";
    import RadioStationForm from "@/lib/widgets/private/form/RadioStationForm.svelte";
    import { resolvePlaceholderImage } from "@/lib/utils";

    export let title;
    export let audience = null;

    let offcanvasRef;
    let radioStationSelected = null;

    const actions = [
        {
            title: "Adicionar rádio",
            icon: "/svg/plus.svg",
            permission: true,
            onClick: () => {
                radioStationSelected = null;
                offcanvasRef.open();
            },
        },
    ];

    function listenersLabel(listeners) {
        if (listeners === 0) return "0 ouvintes";
        return `${Number(listeners).toLocaleString("pt-BR")} ouvintes`;
    }

    function editRadioStation(item) {
        radioStationSelected = item;
        offcanvasRef.open();
    }

    function removeRadioStation(item) {
        if (!window.confirm(`Remover ${item.name} da concorrência?`)) return;

        router.delete(`/panel/reports/radio-station/${item.uuid}`, {
            preserveScroll: true,
        });
    }
</script>

<Offcanvas bind:this={offcanvasRef} title={radioStationSelected?.name ?? "Adicionar rádio"}>
    <div slot="content" let:close>
        {#key radioStationSelected?.uuid ?? "new"}
            <RadioStationForm radioStation={radioStationSelected} {close} />
        {/key}
    </div>
</Offcanvas>

<Section {title} {actions}>
    {#if audience?.data?.length}
        <Carousel class="audience-carousel" label={title} scrollAmount={0.9}>
            {#each audience.data as item (item.uuid)}
                <article class="relative flex h-48 w-36 shrink-0 flex-col overflow-hidden rounded-md bg-blue-ocean font-noto-sans sm:w-40" aria-label={`${item.name}: ${item.status === "online" ? listenersLabel(item.listeners) : "fora do ar"}`}>
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
                    <footer class={["relative flex h-10 shrink-0 items-center justify-center px-2 text-center text-sm font-black uppercase italic text-suspense-aurora",
                        item.status === "online" ? "bg-blue-cerulean" : "bg-red-crimson",
                    ]}>
                        <div class="absolute right-1 bottom-full z-10 mb-1 flex gap-1">
                            <IconButton
                                variant="edit"
                                label="Editar rádio"
                                size="sm"
                                surface="dark"
                                on:click={() => editRadioStation(item)}
                            />
                            <IconButton
                                variant="trash"
                                label="Remover rádio"
                                size="sm"
                                surface="dark"
                                on:click={() => removeRadioStation(item)}
                            />
                        </div>
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
