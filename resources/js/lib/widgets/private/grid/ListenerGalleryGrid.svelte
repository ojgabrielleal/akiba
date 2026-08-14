<script>
    import { router } from "@inertiajs/svelte";

    import { EmptyState, GridList, IconButton, Offcanvas, Pagination, Section } from "@/lib/components/private";
    import { ListenerGalleryForm } from "@/lib/widgets/private";
    import { listenerGalleryPermissions, resolvePlaceholderImage } from "@/lib/utils";

    export let title;
    export let listenerGalleries = { data: [] };

    const can = listenerGalleryPermissions();
    let offcanvasRef;
    let gallerySelected;

    $: offcanvasTitle = gallerySelected ? "Atualizar imagem" : "Cadastrar imagem";

    let actions = [
        {
            title: "Criar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => {
                gallerySelected = null;
                offcanvasRef.open();
            },
        },
    ];

    function requestDestroy(item) {
        router.delete(`/panel/media/listener-gallery/${item.uuid}`, {
            preserveScroll: true,
        });
    }

</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <ListenerGalleryForm {gallerySelected} {close} />
    </div>
</Offcanvas>

<Section {title} {actions}>
    {#if listenerGalleries.data.length > 0}
    <GridList preset="media">
        {#each listenerGalleries.data as item}
            <li>
                <article class="relative aspect-[4/5] w-full overflow-hidden rounded-md bg-blue-ocean">
                    <div class="absolute inset-x-0 top-0 bottom-10">
                        <img
                            src={resolvePlaceholderImage(item.image, "placeholder")}
                            alt={item.caption || `Imagem enviada por ${item.listener_name || "ouvinte"}`}
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="absolute inset-x-0 bottom-0 flex h-10 items-center justify-between gap-2 bg-blue-cerulean px-2">
                        <div class="min-w-0 truncate font-noto-sans text-xs font-extrabold italic uppercase text-suspense-aurora">
                            {item.listener_name || "Ouvinte Akiba"}
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            {#if can.delete}
                                <IconButton
                                    variant="trash"
                                    label="Remover imagem"
                                    size="sm"
                                    surface="dark"
                                    on:click={() => requestDestroy(item)}
                                />
                            {/if}
                            {#if can.update}
                                <IconButton
                                    variant="edit"
                                    label="Atualizar imagem"
                                    size="sm"
                                    surface="dark"
                                    on:click={() => {
                                            gallerySelected = item;
                                            offcanvasRef.open();
                                        }}
                                />
                            {/if}
                        </div>
                    </div>
                </article>
            </li>
        {/each}
    </GridList>
    {:else}
        <EmptyState
            title="Nenhuma imagem encontrada"
            description="As imagens enviadas pelos ouvintes aparecerão aqui."
        />
    {/if}

    {#if listenerGalleries.links}
        <Pagination pages={listenerGalleries} only={["listenerGalleries"]} />
    {/if}
</Section>
