<script>
    import { page, router, Link } from "@inertiajs/svelte";
    import { Section, Offcanvas } from "@/ui/components/private";
    import { MarketingForm } from "@/ui/widgets/private";
    import { repositoryPermissions, resolvePlaceholderImage } from "@/utils";

    $: ({ repositories } = $page.props);

    let can = repositoryPermissions();

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
                fileSelected = [];
                fileType = type;
            },
        },
    ];

    let titles = {
        tutorial: "Tutoriais",
        package: "Pacotes",
        software: "Programas",
    }

    const requestDeactivateRepository = (repository) => {
        router.delete(`/panel/marketing/repository/${repository}`, {},
            { preserveScroll: true },
        );
    };
</script>

<Offcanvas bind:this={offcanvasRef} title={offcanvasTitle}>
    <div slot="content" let:close>
        <MarketingForm {fileSelected} {fileType} {close} />
    </div>
</Offcanvas>

{#each Object.entries(repositories.data) as [type, items]}
    <Section title={titles[type]} actions={getActions(type)}>
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
                            <button type="button"
                                class="cursor-pointer"
                                aria-label={`Atualizar ${item.name}`}
                                on:click={()=>{
                                    offcanvasRef.open();
                                    fileSelected = item;
                                }}
                            >
                                <img
                                    src="/svg/edit.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-4 filter-blue-skywave"
                                    loading="lazy"
                                />
                            </button>
                        {/if}
                        {#if can.deactivate}
                            <button type="button"
                                aria-label={`Desativar ${item.name}`}
                                class="cursor-pointer"
                                on:click={()=>requestDeactivateRepository()}
                            >
                                <img
                                    src="/svg/trash.svg"
                                    alt=""
                                    aria-hidden="true"
                                    class="w-4 filter-red-crimson"
                                    loading="lazy"
                                />
                            </button>
                        {/if}
                    </div>
                </li>
            {/each}
        </ul>
    </Section>
{/each}