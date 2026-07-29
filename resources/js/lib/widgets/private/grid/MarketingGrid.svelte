<script>
    import { router, Link } from "@inertiajs/svelte";

    import { EmptyState, IconButton, Offcanvas, Section } from "@/lib/components/private";
    import { MarketingForm } from "@/lib/widgets/private";
    import { repositoryPermissions, resolvePlaceholderImage } from "@/lib/utils";

    export let repositories = null;

    let titles = {
        tutorial: "Tutoriais",
        package: "Pacotes",
        software: "Programas",
    }

    const can = repositoryPermissions();
    let offcanvasRef;
    let fileType; 
    let fileSelected;

    $:offcanvasTitle = fileSelected?.name ?? "Cadastrar arquivo"

    let getActions = (type) => [
        {
            title: "Criar",
            icon: "/svg/plus.svg",
            permission: can.create,
            onClick: () => {
                offcanvasRef.open();
                fileSelected = null;
                fileType = type;
            },
        },
    ];

    function requestDeactivateRepository(repository) {
        router.patch(`/panel/marketing/repository/${repository.uuid}/deactivate`, {},
            { preserveScroll: true },
        );
    }
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <MarketingForm {fileSelected} {fileType} {close} />
    </div>
</Offcanvas>

{#each Object.entries(repositories?.data ?? {}) as [type, items]}
    <Section title={titles[type]} actions={getActions(type)}>
        {#if items.length}
            <ul class="mb-20 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-x-4 gap-y-15">
                {#each items as item}
                <li class="relative w-full bg-blue-ocean rounded-t-lg rounded-b-md">
                    <Link
                        aria-label={`Visitar ${item.name}`}
                        href={item.url}
                        target="_blank"
                    >
                        <img
                            src={resolvePlaceholderImage(item.image, "placeholder")}
                            alt={item.name}
                            class="w-full h-45 object-cover aspect-square rounded-t-md"
                            loading="lazy"
                        />
                        <div class="p-2 text-suspense-aurora text-center font-noto-sans font-light">
                            {item.name}
                        </div>
                    </Link>
                    <div class="absolute -bottom-8 right-0 flex flex-row gap-3">
                        {#if can.update}
                            <IconButton
                                variant="edit"
                                label="Atualizar"
                                surface="transparent"
                                tone="primary"
                                on:click={()=>{
                                    offcanvasRef.open();
                                    fileSelected = item;
                                }}
                            />
                        {/if}
                        {#if can.deactivate}
                            <IconButton
                                variant="trash"
                                label="Desativar"
                                surface="transparent"
                                on:click={()=>requestDeactivateRepository(item)}
                            />
                        {/if}
                    </div>
                </li>
                {/each}
            </ul>
        {:else}
            <EmptyState
                title={`Nenhum item em ${titles[type]?.toLowerCase() ?? "marketing"}`}
                description="Os arquivos cadastrados nesta categoria aparecerão aqui."
            />
        {/if}
    </Section>
{/each}
