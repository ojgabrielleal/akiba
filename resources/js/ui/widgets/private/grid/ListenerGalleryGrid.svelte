<script>
    export let title;

    import { page, router } from "@inertiajs/svelte";
    import { ButtonPagination, Offcanvas, Section, Tooltip } from "@/ui/components/private";
    import { ListenerGalleryForm } from "@/ui/widgets/private";
    import { listenerGalleryPermissions, resolvePlaceholderImage } from "@/utils";

    let can = listenerGalleryPermissions();

    let offcanvasRef;
    let gallerySelected;
    $: offcanvasTitle = gallerySelected ? "Atualizar imagem" : "Cadastrar imagem";

    $: ({ listenerGalleries = { data: [] } } = $page.props);

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

    const requestDestroy = (item) => {
        router.delete(`/panel/media/listener-gallery/${item.uuid}`, {
            preserveScroll: true,
        });
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <ListenerGalleryForm {gallerySelected} {close} />
    </div>
</Offcanvas>

<Section {title} {actions}>
    <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        {#each listenerGalleries.data as item}
            <li>
                <article class="relative aspect-[4/5] w-full overflow-hidden rounded-md bg-blue-ocean">
                    <img
                        src={resolvePlaceholderImage(item.image, "placeholder")}
                        alt={item.caption || `Imagem enviada por ${item.listener_name || "ouvinte"}`}
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div class="absolute inset-x-0 bottom-0 flex h-7 items-center justify-end gap-1 bg-blue-cerulean px-2 py-5">
                        {#if can.delete}
                            <Tooltip>
                                <button
                                    type="button"
                                    aria-label={`Remover imagem de ${item.listener_name || "ouvinte"}`}
                                    class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-blue-night"
                                    on:click={() => requestDestroy(item)}
                                >
                                    <img
                                        src="/svg/trash.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-4 filter-red-crimson"
                                    />
                                </button>
                                <div slot="content">Remover</div>
                            </Tooltip>
                        {/if}
                        {#if can.update}
                            <Tooltip>
                                <button
                                    type="button"
                                    aria-label={`Editar imagem de ${item.listener_name || "ouvinte"}`}
                                    class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-blue-night"
                                    on:click={() => {
                                        gallerySelected = item;
                                        offcanvasRef.open();
                                    }}
                                >
                                    <img
                                        src="/svg/edit.svg"
                                        alt=""
                                        aria-hidden="true"
                                        class="w-4 filter-orange-citric"
                                    />
                                </button>
                                <div slot="content">Atualizar</div>
                            </Tooltip>
                        {/if}
                    </div>
                </article>
            </li>
        {/each}
    </ul>

    {#if listenerGalleries.links}
        <ButtonPagination pages={listenerGalleries} only={["listenerGalleries"]} />
    {/if}
</Section>
